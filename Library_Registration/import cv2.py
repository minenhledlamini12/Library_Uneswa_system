import cv2
import numpy as np
import pyzbar.pyzbar as pyzbar
import urllib.request
import base64
from Crypto.Cipher import AES
import mysql.connector
from datetime import datetime

# AES Configuration
ENCRYPTION_KEY = b"Pa@47781"  # Must match PHP encryption key
IV_LENGTH = 16

# Database configuration
DB_CONFIG = {
    'host': '127.0.0.1',  # XAMPP default host
    'user': 'root',       # XAMPP default user
    'password': '',       # XAMPP default password (empty)
    'database': 'library'
}

def decrypt_data(encrypted_data):
    try:
        raw_data = base64.b64decode(encrypted_data)
        iv = raw_data[:IV_LENGTH]
        ciphertext = raw_data[IV_LENGTH:]
        cipher = AES.new(ENCRYPTION_KEY, AES.MODE_CBC, iv)
        decrypted = cipher.decrypt(ciphertext)
        return decrypted[:-decrypted[-1]].decode('utf-8')
    except Exception as e:
        return f"Decryption error: {str(e)}"

def verify_member(email):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        # Query to check if email exists in members table
        query = "SELECT * FROM members WHERE Email = %s"
        cursor.execute(query, (email,))
        member = cursor.fetchone()
        
        if member:
            # Log entry time in a separate table (optional)
            log_query = "INSERT INTO library_access_log (Member_ID, Email, Entry_Time) VALUES (%s, %s, %s)"
            current_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            cursor.execute(log_query, (member['Member_ID'], email, current_time))
            conn.commit()
            
            return True, member
        else:
            return False, None
            
    except Exception as e:
        print(f"Database error: {str(e)}")
        return False, None
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

# Camera setup
url = 'http://192.168.1.61/'
cv2.namedWindow("Library Access System", cv2.WINDOW_AUTOSIZE)

prev_data = ""
while True:
    try:
        # Get frame from IP camera
        img_resp = urllib.request.urlopen(url + 'cam-hi.jpg')
        imgnp = np.array(bytearray(img_resp.read()), dtype=np.uint8)
        frame = cv2.imdecode(imgnp, -1)

        # QR Code Detection
        decoded_objects = pyzbar.decode(frame)
        for obj in decoded_objects:
            if prev_data != obj.data:
                try:
                    # Decrypt the scanned data
                    decrypted_email = decrypt_data(obj.data)
                    
                    # Verify against database
                    is_valid, member_data = verify_member(decrypted_email)
                    
                    if is_valid:
                        status = "ACCESS GRANTED"
                        color = (0, 255, 0)  # Green
                        member_info = f"ID: {member_data['Member_ID']} | Name: {member_data['Name']} {member_data['Surname']}"
                    else:
                        status = "ACCESS DENIED"
                        color = (0, 0, 255)  # Red
                        member_info = "Member not found in database"
                    
                    # Display results on frame
                    cv2.putText(frame, status, (30, 50), cv2.FONT_HERSHEY_SIMPLEX, 1, color, 2)
                    cv2.putText(frame, decrypted_email, (30, 90), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
                    cv2.putText(frame, member_info, (30, 130), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
                    
                    prev_data = obj.data
                    print(f"\n--- QR Code Scan Result ---")
                    print(f"Email: {decrypted_email}")
                    print(f"Status: {status}")
                    if is_valid:
                        print(f"Member: {member_data['Name']} {member_data['Surname']}")
                        print(f"Department: {member_data['Course/Department/Affliation']}")
                        
                except Exception as e:
                    print(f"Processing error: {str(e)}")

        cv2.imshow("Library Access System", frame)

        if cv2.waitKey(1) == 27:  # ESC key to exit
            break

    except Exception as e:
        print(f"Camera error: {str(e)}")
        continue

cv2.destroyAllWindows()
