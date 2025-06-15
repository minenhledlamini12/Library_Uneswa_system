import cv2
import numpy as np
import pyzbar.pyzbar as pyzbar
import urllib.request
import mysql.connector
from datetime import datetime
import time
import requests
from base64 import b64decode
from Crypto.Cipher import AES
from Crypto.Util.Padding import unpad

# --- Configuration ---

DB_CONFIG = {
    'host': '127.0.0.1',  # Update if needed
    'user': 'root',
    'password': '',
    'database': 'library'
}

CAMERA_URL = 'http://192.168.15.245/cam-hi.jpg'  # Your ESP32-CAM image URL
ESP32_IP = '192.168.15.69'  # Your ESP32 door controller IP

# --- AES Decryption Function ---
def decrypt_data(encrypted_data_b64, key):
    try:
        encrypted_data = b64decode(encrypted_data_b64)
        iv = encrypted_data[:16]
        ciphertext = encrypted_data[16:]
        # Pad or truncate key to 32 bytes (AES-256)
        key_bytes = key.encode('utf-8')
        if len(key_bytes) < 32:
            key_bytes = key_bytes.ljust(32, b'\0')
        else:
            key_bytes = key_bytes[:32]
        cipher = AES.new(key_bytes, AES.MODE_CBC, iv)
        decrypted = unpad(cipher.decrypt(ciphertext), AES.block_size)
        return decrypted.decode('utf-8')
    except Exception as e:
        print(f"Decryption error: {e}")
        return None

# --- Functions to send commands to ESP32 ---

def send_scanning(data):
    try:
        url = f"http://{ESP32_IP}/scanning?data={data}"
        response = requests.get(url, timeout=2)
        print("Scanning signal sent:", response.text)
    except requests.RequestException as e:
        print("Failed to send scanning signal:", e)

def send_access_granted(user_email):
    try:
        url = f"http://{ESP32_IP}/access-granted?user={user_email}"
        response = requests.get(url, timeout=2)
        print("Access granted signal sent:", response.text)
    except requests.RequestException as e:
        print("Failed to send access granted signal:", e)

def send_access_denied():
    try:
        url = f"http://{ESP32_IP}/access-denied"
        response = requests.get(url, timeout=2)
        print("Access denied signal sent:", response.text)
    except requests.RequestException as e:
        print("Failed to send access denied signal:", e)

# --- Database verification function ---

def verify_member(email):
    """Check if email exists in DB and log access with status 'in'."""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)

        query = "SELECT ID, Name, Surname FROM members WHERE Email = %s"
        cursor.execute(query, (email,))
        member = cursor.fetchone()
        # Ensure all results are read before next query
        cursor.fetchall()

        if member:
            # Log the entry with status 'in'
            log_query = """
                INSERT INTO library_access_log (Member_ID, Email, Entry_Time, status)
                VALUES (%s, %s, %s, %s)
            """
            current_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            cursor.execute(log_query, (member['ID'], email, current_time, 'in'))
            conn.commit()
            return True, member
        else:
            return False, None

    except mysql.connector.Error as err:
        print(f"MySQL error in verify_member: {err}")
        return False, None

    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'conn' in locals() and conn.is_connected():
            conn.close()

# --- Main loop ---

def main():
    cv2.namedWindow("Library Access System", cv2.WINDOW_AUTOSIZE)
    prev_data = None
    cooldown = 10  # seconds cooldown between scans
    last_scan_time = 0
    decryption_key = "Pa@47781"  # Must match PHP encryption key

    while True:
        try:
            img_resp = urllib.request.urlopen(CAMERA_URL)
            imgnp = np.array(bytearray(img_resp.read()), dtype=np.uint8)
            frame = cv2.imdecode(imgnp, cv2.IMREAD_COLOR)

            decoded_objects = pyzbar.decode(frame)

            for obj in decoded_objects:
                qr_data_bytes = obj.data
                try:
                    qr_data = qr_data_bytes.decode('utf-8')
                except UnicodeDecodeError:
                    print("Error decoding QR data as UTF-8. Skipping this QR code.")
                    continue

                # Send scanning signal immediately when QR detected
                send_scanning(qr_data)

                if qr_data != prev_data or (time.time() - last_scan_time) > cooldown:
                    print(f"QR data (encrypted): {qr_data}")

                    # --- DECRYPT QR CODE DATA ---
                    email = decrypt_data(qr_data, decryption_key)
                    print(f"Decrypted email: {email}")

                    if email:
                        is_valid, member_data = verify_member(email)
                    else:
                        is_valid, member_data = False, None

                    if is_valid:
                        status = "ACCESS GRANTED"
                        color = (0, 255, 0)
                        member_info = f"Welcome {member_data['Name']} {member_data['Surname']}"
                        send_access_granted(email)
                    else:
                        status = "ACCESS DENIED"
                        color = (0, 0, 255)
                        member_info = "Member not found"
                        send_access_denied()

                    # Display on frame
                    cv2.putText(frame, status, (30, 50), cv2.FONT_HERSHEY_SIMPLEX, 1, color, 2)
                    cv2.putText(frame, email if email else "Invalid QR", (30, 90), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
                    cv2.putText(frame, member_info, (30, 130), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)

                    prev_data = qr_data
                    last_scan_time = time.time()

            cv2.imshow("Library Access System", frame)

            if cv2.waitKey(1) == 27:  # ESC key to exit
                break

        except urllib.error.URLError as e:
            print(f"Error accessing camera URL: {e}")
            print("Please check if the ESP32-CAM is powered on and the URL is correct.")
            time.sleep(5)
        except Exception as e:
            print(f"An unexpected error occurred: {e}")
            time.sleep(1)

    cv2.destroyAllWindows()

if __name__ == "__main__":
    main()
