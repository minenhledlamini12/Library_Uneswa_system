<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Book</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f8f0;
            color: #333;
        }

        .top-bar {
            background-color: #004085;
            color: white;
            text-align: right;
            padding: 5px 20px;
            font-size: 0.8em;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            margin-left: 5px;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        header img {
            height: 50px;
            margin-right: 10px;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-right {
            text-align: right;
        }

        nav {
            display: flex;
            justify-content: center;
            background-color: #388E3C;
            padding: 10px 0;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        main {
            text-align: center;
            padding: 20px;
        }

        .borrow-form {
            margin: 20px auto;
            max-width: 600px;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            text-align: left;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .borrow-button {
            width: 100%;
            height: 40px;
            padding: 10px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .borrow-button:hover {
            background-color: #3e8e41;
        }

        /* QR Code Scanner Styles */
        #qr-video {
            width: 100%;
            max-width: 400px;
            margin: 10px auto;
            border: 1px solid #ddd;
        }

        #bookDetails {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }

        footer {
            background-color: #ccc;
            color: #333;
            padding: 20px 0;
            font-size: 0.9em;
            margin-top: 20px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .footer-section h3 {
            margin-top: 0;
            color: #333;
        }

        .footer-section ul {
            list-style-type: none;
            padding-left: 0;
        }

        .footer-section a {
            color: #333;
            text-decoration: none;
        }

        .footer-section a:hover {
            text-decoration: underline;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.2);
        }

        .icon {
            margin-right: 5px;
        }
    </style>
</head>

<body>

    <div class="top-bar">
        <span><i class="far fa-clock icon"></i> Mon - Fri: 08:30 AM - 11:00 PM, Sat: 10:00 AM - 05:00 PM, Sun: 03:00 PM - 10:00 PM &nbsp;&nbsp; <i class="fas fa-phone icon"></i> 2517 0448
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </span>
    </div>

    <header>
        <div class="header-left">
            <img src="/php_program/Barrowing_system/Images/download.png" alt="UNESWA Library Logo">
            <h1>University of Eswatini Library</h1>
        </div>
        <div class="header-right">
            Kwaluseni Campus - Self-Service Book Borrowing
        </div>
    </header>

    <nav>
        <a href="homepage.php"><i class="fas fa-home icon"></i> Home</a>
        <a href="search.php"><i class="fas fa-search icon"></i> Search Book</a>
        <a href="#library-regulations"><i class="fas fa-file-alt icon"></i> Library Regulations</a>
    </nav>

    <main>
        <div class="borrow-form">
            <h2>Borrow Book</h2>
            <video id="qr-video"></video>
            <form action="process_borrow.php" method="post" id="borrowForm">
                <!-- ISBN Input (Hidden) -->
                <input type="hidden" id="bookISBN" name="bookISBN">
                <input type="hidden" id="memberType" name="memberType">
                 <input type="hidden" id="memberEmail" name="memberEmail">

                <!-- Book Details Display -->
                <div id="bookDetails" style="display: none;">
                    <div class="form-group">
                        <label for="bookTitle">Book Title:</label>
                        <input type="text" id="bookTitle" name="bookTitle" readonly>
                    </div>
                    <div class="form-group">
                        <label for="bookAuthor">Book Author:</label>
                        <input type="text" id="bookAuthor" name="bookAuthor" readonly>
                    </div>
                </div>

                <!-- Member Type Selection -->
                <div class="form-group">
                    <label for="memberCategory">Member Category:</label>
                    <select id="memberCategory" name="memberCategory" required onchange="getMemberDetails(this.value)">
                        <option value="">Select Category</option>
                        <option value="Student">Student</option>
                        <option value="Staff">Staff</option>
                        <option value="External">External Member</option>
                    </select>
                </div>

                <!-- Borrower Details (Hidden) -->
                 <input type="hidden" id="borrowerName" name="borrowerName">
                 <input type="hidden" id="borrowerEmail" name="borrowerEmail">

                <div class="form-group">
                    <label for="borrowDate">Borrow Date:</label>
                    <input type="date" id="borrowDate" name="borrowDate" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="returnDate">Return Date:</label>
                    <input type="date" id="returnDate" name="returnDate" readonly>
                </div>

                <!-- Terms and Conditions -->
                <div class="form-group">
                    <a href="library_regulations.php" target="_blank">View Library Terms and Conditions</a>
                    <input type="checkbox" id="termsAgreed" name="termsAgreed" required>
                    <label for="termsAgreed">I agree to the library terms and conditions.</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="borrow-button">Borrow Book</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-section get-in-touch">
                <h3>Get In Touch</h3>
                <img src="/php_program/Barrowing_system/Images/download.png" alt="University of Eswatini Library Logo" style="height:50px;">
                <p>Kwaluseni, Luyengo & Mbabane</p>
                <p><i class="fas fa-phone icon"></i> 2517 0448</p>
                <p><i class="fas fa-envelope icon"></i> <a href="mailto:library@uniswa.sz">library@uniswa.sz</a></p>
            </div>

            <div class="footer-section quick-links">
                <h3>Quick Links</h3>
                <ul>
                    <li>Eswatini National Bibliography</li>
                    <li>UNESWA IR</li>
                    <li>Notices</li>
                    <li>Past Exam Papers</li>
                    <li>UNESWA</li>
                </ul>
            </div>

            <div class="footer-section popular-databases">
                <h3>Popular Databases</h3>
                <ul>
                    <li>Science Direct</li>
                    <li>Ebscohost</li>
                    <li>ERIC</li>
                    <li>Taylor & Francis</li>
                    <li>Sabinet</li>
                </ul>
            </div>

            <div class="footer-section follow-us">
                <h3>Follow Us</h3>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; <?php echo date("Y"); ?> University of Eswatini Library | All Rights Reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <script>
        const video = document.getElementById("qr-video");
        let scanning = false;
        const encryptionKey = "Pa@47781"; // Ensure this matches the encryption key in your PHP script

        function startScan() {
            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: "environment"
                }
            }).then(function(stream) {
                video.srcObject = stream;
                video.setAttribute("playsinline", true);
                video.play();
                scanning = true;
                requestAnimationFrame(scan);
            }).catch(function(err) {
                console.error("Error accessing camera: " + err);
                alert("Error accessing camera: " + err);
            });
        }

        function scan() {
            if (!scanning) return;

            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                const canvasElement = document.createElement('canvas');
                canvasElement.width = video.videoWidth;
                canvasElement.height = video.videoHeight;
                const canvasContext = canvasElement.getContext('2d');
                canvasContext.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                const imageData = canvasContext.getImageData(0, 0, canvasElement.width, canvasElement.height);

                const code = jsQR(imageData.data, imageData.width, canvasElement.height, {
                    inversionAttempts: "dontInvert",
                });

                if (code) {
                    console.log("Found QR code", code.data);
                    const encryptedISBN = code.data;
                    const isbn = decryptData(encryptedISBN, encryptionKey);
                    console.log("Decrypted ISBN:", isbn);
                    document.getElementById('bookISBN').value = isbn;
                    getBookDetails(isbn);
                    stopScan();
                    return;
                }
            }
            requestAnimationFrame(scan);
        }

        function stopScan() {
            scanning = false;
            if (video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
            }
        }

        function decryptData(encryptedData, key) {
            try {
                const keyUTF = CryptoJS.enc.Utf8.parse(key);
                const iv = CryptoJS.enc.Base64.parse(encryptedData.substring(0, 24));
                const encrypted = CryptoJS.enc.Base64.parse(encryptedData.substring(24));

                const decrypted = CryptoJS.AES.decrypt({
                    ciphertext: encrypted
                }, keyUTF, {
                    iv: iv,
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                });

                return decrypted.toString(CryptoJS.enc.Utf8);
            } catch (error) {
                console.error("Decryption Error:", error);
                alert("Decryption failed. Please ensure the QR code is valid.");
                return null;
            }
        }

         function getBookDetails(isbn) {
         fetch('get_book_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'bookISBN=' + encodeURIComponent(isbn)
            })
             .then(response => {
                  if (!response.ok) {
                  throw new Error(`HTTP error! Status: ${response.status}`);
                  }
                 return response.json();
                })
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('bookTitle').value = data.book.Title;
                    document.getElementById('bookAuthor').value = data.book.Author;
                    document.getElementById('bookDetails').style.display = 'block';
                } else {
                    alert('Book not found.');
                    document.getElementById('bookDetails').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching book details: ' + error);
            });
        }

                function getMemberDetails(memberType) {
                    if (memberType === "") {
                        alert("Please select a member category.");
                        return;
                    }
                    document.getElementById('memberType').value = memberType;
                    fetch('get_member_details.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'memberType=' + encodeURIComponent(memberType)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById('memberType').value = memberType;
                            document.getElementById('memberEmail').value = data.data.Email;
                            document.getElementById('borrowerName').value = data.data.Name;
                            document.getElementById('borrowerEmail').value = data.data.Email;


                         // Calculate return date based on member type
                         var borrowDate = document.getElementById('borrowDate').value;
                            if (!borrowDate) {
                                alert("Please select a borrow date.");
                                return;
                            }

                            let returnDate = calculateReturnDate(borrowDate, memberType);
                            document.getElementById('returnDate').value = returnDate;

                        } else {
                           alert('Error fetching member details: ' + data.message);
                            document.getElementById('memberType').value = "";
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error fetching member details.');
                    });
                }

                 function calculateReturnDate(borrowDate, memberType) {
                 let returnDate = new Date(borrowDate);
                switch (memberType) {
                   case 'Student':
                      returnDate.setDate(returnDate.getDate() + 14); // 14 days for students
                        break;
                    case 'Staff':
                        returnDate.setDate(returnDate.getDate() + 84); // 12 weeks (84 days) for staff
                        break;
                    case 'External':
                        returnDate.setDate(returnDate.getDate() + 14); // 14 days for external members
                        break;
                    default:
                       alert('Invalid member type.');
                       return null;
                     }
                // Format date as YYYY-MM-DD for input type="date"
                   return returnDate.toISOString().slice(0, 10);
                    }

        // Start scanning on page load
        startScan();
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
