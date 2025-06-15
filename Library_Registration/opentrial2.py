import cv2
import pyzbar.pyzbar as pyzbar
import requests
from PIL import Image
from io import BytesIO
import base64
from cryptography.hazmat.primitives import padding
from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
import hashlib

# Replace this with your ESP32 CAM's IP address
url = "http://192.168.27.245/"

def decrypt_qr_code_data(encrypted_data, key):
    # Derive the encryption key (same as in PHP)
    derived_key = hashlib.sha256(key.encode()).digest()
    
    # Decode base64 and extract IV and ciphertext
    decoded_data = base64.b64decode(encrypted_data)
    iv_size = 16  # AES-256-CBC uses a 16-byte IV
    iv = decoded_data[:iv_size]
    ciphertext = decoded_data[iv_size:]
    
    # Decrypt the data
    cipher = Cipher(algorithms.AES(derived_key), modes.CBC(iv))
    decryptor = cipher.decryptor()
    decrypted_padded_data = decryptor.update(ciphertext) + decryptor.finalize()
    
    # Remove padding
    unpadder = padding.PKCS7(128).unpadder()
    decrypted_data = unpadder.update(decrypted_padded_data) + unpadder.finalize()
    
    return decrypted_data.decode("utf-8")

def capture_and_decode_qr_code(url):
    # Fetch the image from the ESP32 CAM
    try:
        response = requests.get(url)
        img = Image.open(BytesIO(response.content))
        
        # Convert to OpenCV format
        import numpy as np
        frame = cv2.cvtColor(np.array(img), cv2.COLOR_RGB2BGR)
        
        # Detect and decode QR codes
        qr_codes = pyzbar.decode(frame)
        
        for qr_code in qr_codes:
            # Get the QR code data
            qr_code_data = qr_code.data.decode("utf-8")
            
            # Decrypt the QR code data
            encryption_key = "Pa@47781"
            decrypted_data = decrypt_qr_code_data(qr_code_data, encryption_key)
            
            if decrypted_data:
                print(f"Decrypted QR Code Data: {decrypted_data}")
    except requests.exceptions.RequestException as e:
        print(f"Request failed: {e}")

# Call the function to capture and decode QR codes
capture_and_decode_qr_code(url)
