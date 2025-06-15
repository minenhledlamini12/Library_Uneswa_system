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

# --- Configuration ---
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'library'
}

CAMERA_URL = 'http://192.168.15.245/cam-hi.jpg'
ESP32_IP = '192.168.15.149'
ENCRYPTION_KEY = "Pa@47781"

# --- AES Decryption Function ---
def decrypt_data(encrypted_data_b64, key):
    try:
        encrypted_data = b64decode(encrypted_data_b64)
        iv = encrypted_data[:16]
        ciphertext = encrypted_data[16:]
        key_bytes = key.encode('utf-8')
        key_bytes = key_bytes.ljust(32, b'\0')[:32]
        cipher = AES.new(key_bytes, AES.MODE_CBC, iv)
        decrypted = cipher.decrypt(ciphertext)
        pad_len = decrypted[-1]
        if pad_len < 1 or pad_len > 16:
            print("Invalid padding length detected during decryption.")
            return None
        decrypted = decrypted[:-pad_len]
        return decrypted.decode('utf-8')
    except Exception as e:
        print(f"Decryption error: {e}")
        return None

# --- ESP32 Communication Functions ---
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

# --- Get Name and Surname Function ---
def get_member_name(email):
    conn = None
    cursor = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        query = "SELECT Name, Surname FROM members WHERE Email = %s LIMIT 1"
        cursor.execute(query, (email,))
        row = cursor.fetchone()
        if row:
            return row['Name'], row['Surname']
        else:
            return None, None
    except mysql.connector.Error as err:
        print(f"MySQL error in get_member_name: {err}")
        return None, None
    finally:
        if cursor:
            cursor.close()
        if conn and conn.is_connected():
            conn.close()

# --- Log Exit Function ---
def log_exit(email):
    """
    Updates the latest log entry for the member with the current exit time and sets status to 'out'.
    Returns True if update was successful, False otherwise.
    """
    conn = None
    cursor = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        # Find the latest entry with status='in' and Exit_Time IS NULL
        select_query = """
            SELECT Log_ID FROM library_access_log
            WHERE Email = %s AND status = 'in' AND Exit_Time IS NULL
            ORDER BY Entry_Time DESC LIMIT 1
        """
        cursor.execute(select_query, (email,))
        result = cursor.fetchone()
        if result:
            log_id = result[0]
            exit_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            update_query = """
                UPDATE library_access_log
                SET Exit_Time = %s, status = 'out'
                WHERE Log_ID = %s
            """
            cursor.execute(update_query, (exit_time, log_id))
            conn.commit()
            return True
        else:
            print("No open entry log found for this user.")
            return False
    except mysql.connector.Error as err:
        print(f"MySQL error in log_exit: {err}")
        return False
    finally:
        if cursor:
            cursor.close()
        if conn and conn.is_connected():
            conn.close()

# --- Main Loop ---
def main():
    cv2.namedWindow("Library Access System", cv2.WINDOW_AUTOSIZE)
    prev_data = None
    cooldown = 10  # seconds cooldown between scans
    last_scan_time = 0

    while True:
        try:
            img_resp = urllib.request.urlopen(CAMERA_URL)
            imgnp = np.array(bytearray(img_resp.read()), dtype=np.uint8)
            frame = cv2.imdecode(imgnp, cv2.IMREAD_COLOR)
            decoded_objects = pyzbar.decode(frame)

            for obj in decoded_objects:
                qr_data_bytes = obj.data
                try:
                    qr_data_encrypted = qr_data_bytes.decode('utf-8')
                except UnicodeDecodeError:
                    print("Error decoding QR data as UTF-8. Skipping this QR code.")
                    continue

                # Decrypt QR code data
                qr_data = decrypt_data(qr_data_encrypted, ENCRYPTION_KEY)
                if qr_data is None:
                    print("Failed to decrypt QR code data. Skipping.")
                    continue

                # Send scanning signal immediately when QR detected
                send_scanning(qr_data)

                # Only process if new or cooldown expired
                if qr_data != prev_data or (time.time() - last_scan_time) > cooldown:
                    print(f"QR data (decrypted email): {qr_data}")

                    # --- EXIT LOGIC ---
                    success = log_exit(qr_data)
                    if success:
                        name, surname = get_member_name(qr_data)
                        status = "EXIT LOGGED"
                        color = (0, 255, 255)
                        member_info = f"Goodbye {name} {surname}" if name and surname else "Goodbye"
                        send_access_granted(qr_data)
                    else:
                        status = "NO ENTRY FOUND"
                        color = (0, 0, 255)
                        member_info = "No entry log found"
                        send_access_denied()

                    # Display on frame
                    cv2.putText(frame, status, (30, 50), cv2.FONT_HERSHEY_SIMPLEX, 1, color, 2)
                    cv2.putText(frame, qr_data, (30, 90), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
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
