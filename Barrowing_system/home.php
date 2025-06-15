<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNESWA Library - Self Service Book Borrowing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
      body {
          font-family: sans-serif;
          margin: 0;
          padding: 0;
          background-color: #f4f4f4; /* Light Grey Background for the whole page */
      }
      .container {
          max-width: 1200px;
          margin: 20px auto;
          padding: 20px;
      }
      .frame {
          border: 1px solid #ddd;
          padding: 15px;
          margin-bottom: 20px;
          background-color: #fff; /* White background for most frames */
          display: flex;
          align-items: center;
          border-radius: 5px; /* Rounded corners for frames */
          box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Subtle shadow for depth */
      }
      .frame h2 {
          margin-top: 0;
          color: #333;
      }
      .frame img {
          max-width: 200px; /* Adjust as needed */
          margin-right: 15px; /* Space between image and text */
          margin-left: 15px;
      }
      .frame.image-right img {
          order: 1; /* Puts the image on the right */
          margin-left: auto; /* Push image to the right */
          margin-right: 0;
      }
      .frame.image-right {
          flex-direction: row-reverse; /* Reverse the order of elements */
      }
      /* Responsive Styling */
      @media (max-width: 768px) {
          .frame {
              flex-direction: column; /* Stack image and text vertically */
              text-align: center; /* Center the text */
          }
          .frame img {
              margin: 0 auto 15px; /* Center image and add space below */
              max-width: 150px; /* Adjust image size for smaller screens */
          }
          .frame.image-right {
              flex-direction: column; /* Keep stacking on smaller screens */
          }
          .frame.image-right img {
              margin: 0 auto 15px; /* Center image and add space below */
          }
      }
        /* Notice Frame Styling */
        .notice-frame {
            background-color: #f0f0f0; /* Light Grey Background */
            padding: 20px;
            border-radius: 5px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1); /* Subtle inset shadow */
        }

        .notice-frame h3 {
            color: #555;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .notice-frame li {
            margin-bottom: 10px;
        }

        .notice-frame a {
            color: #007bff;
            text-decoration: none;
        }

        .notice-frame a:hover {
            text-decoration: underline;
        }
      /* Contact Frame Styling */
      .contact-frame {
          background-color: #003366; /* Dark Blue Background */
          color: white;
          padding: 20px;
          border-radius: 5px; /* Optional: Rounded corners */
      }

      .contact-frame h2 {
          color: white;
          margin-bottom: 15px;
      }

      .contact-frame p {
          margin-bottom: 10px;
      }

      .contact-frame a {
          color: #4CAF50; /* Green Link Color */
          text-decoration: none;
      }

      .contact-frame a:hover {
          text-decoration: underline;
      }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container">

        <section class="frame" id="purpose">
            <img src="https://via.placeholder.com/200" alt="Purpose Image">
            <div>
              <h2>Purpose of this Page</h2>
              <p>This page provides a self-service book borrowing system for the University of Eswatini Library. Registered students and staff can browse, search, and request books online.</p>
            </div>
        </section>

        <section class="frame image-right" id="library-description">
            <img src="https://via.placeholder.com/200" alt="Library Description Image">
            <div>
                <h2>UNESWA Library</h2>
                <p>The University of Eswatini Library consists of three decentralised units, each with its staff and stock, coordinated at Kwaluseni. The stock of each unit is available to the other Libraries through an internal loan system. The stock consists chiefly of undergraduate textbooks, journals, reference materials and special collections.</p>
                <p>The Kwaluseni Library is the main library, serving the Faculties of Commerce, Education, Humanities, Science & Engineering, Social Sciences, the Institute of Distance Education, and the Institute of Post-Graduate Studies. The library has a sitting capacity of over 700 users at a time with separate study carrels for post-graduate students and lecturers.</p>
            </div>
        </section>

        <section class="frame" id="services">
            <img src="https://via.placeholder.com/200" alt="Services Image">
            <div>
              <h2>UNESWA Library Services</h2>
              <ul>
                  <li>Print, Copy & Scan</li>
                  <li>Computer labs & Apple Hub of Creativity</li>
                  <li>Growing collection of e-resources and e-books</li>
                  <li>Academic Reserves</li>
                  <li>Inter-library loans</li>
                  <li>Research support</li>
                  <li>ISBN Subscription</li>
                  <li>External Membership (Individuals and institutions)</li>
              </ul>
           </div>
        </section>

        <section class="frame image-right" id="notices">
            <div class = "notice-frame">
              <h2>Notices</h2>
              <ul>
                  <li><strong>Research4Life</strong>
                    <br>All Staff & Students - 19 January 2024
                    <br>We are pleased to announce that we have added Research4Life to our e-resources collection. Explore this exciting addition by navigating to "E-resources" -> "Databases A-Z" on our website.
                    <br>For login credentials, kindly refer to the Library memo dated the 19th of January 2024 or visit the library. Enhance your research journey with Research4Life!
                  </li>
                  <li><strong>Remote Access</strong>
                    <br>All Staff & Students - 28 August 2023
                    <br>Only registered Open Athens users can access the resources from remote. If you have not yet registered for remote access send your Name, Department, email along with a copy of your UNESWA ID to <a href="mailto:anbu@uniswa.sz">anbu@uniswa.sz</a>. On registration you will receive a confirmation email from Open Athens. Use this facility responsibly.
                  </li>
              </ul>
            </div>
        </section>

        <section class="frame" id="staff">
            <img src="https://via.placeholder.com/200" alt="Staff Image">
            <div>
                <h2>Library Staff</h2>
                <p>I am unable to extract the staff information automatically from the provided link due to security configurations. Here's some staff</p>
                <p><strong>[Staff Name]:</strong> [Title] - [Contact Information]</p>
                <p><strong>[Staff Name]:</strong> [Title] - [Contact Information]</p>
                <p><strong>[Staff Name]:</strong> [Title] - [Contact Information]</p>
            </div>
        </section>

        <section class="frame image-right contact-frame" id="contact">
            <img src="https://pplx-res.cloudinary.com/image/upload/v1741304231/user_uploads/qguwaUAtVusBfHa/Screenshot-2025-03-07-013632.jpg" alt="Contact Map">
            <div>
              <h2>Contact Us</h2>
              <p>If You Have Any Query, Please Contact Us</p>
              <form>
                <input type="text" placeholder="Your Name">
                <input type="email" placeholder="Your Email">
                <input type="text" placeholder="Subject">
                <textarea placeholder="Message"></textarea>
                <button type="submit">Send Message</button>
              </form>
            </div>
        </section>

    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
