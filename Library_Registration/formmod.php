<!DOCTYPE html>
<html>
<head>
    <title>UNESWA Library Registration</title>
    <style>
     <style>
        body {
            background-color: darkgreen;
            font-family: Arial, sans-serif;
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 10px;
            width: 500px;
            text-align: center;
        }

        h1 {
            color: #fff;
        }

        label {
            display: block;
            margin-top: 10px;
            text-align: left;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            color: black; /* Ensure text is readable */
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to UNESWA Library</h1>
        <h2>Registration</h2>
        <form method="post" action="register.php">  <label for="Student_ID">Student ID:</label>
            <input type="number" id="Student_ID" name="Student_ID" required><br>

            <label for="Student_name">Name:</label>
            <input type="text" id="Student_name" name="Student_name" required><br>

            <label for="Surname">Surname:</label>
            <input type="text" id="Surname" name="Surname" required><br>

            <label for="Course/Department/Affliation">Course/Department/Affiliation:</label>
            <input type="text" id="Course/Department/Affliation" name="Course/Department/Affliation" required><br>

            <label for="Membership_Type">Membership Type:</label>
            <select id="Membership_Type" name="Membership_Type" required>
                <option value="Student">Student</option>
                <option value="Staff">Staff</option>
                <option value="External Member">External Member</option>
            </select><br>

            <label for="Contact">Contact:</label>
            <input type="text" id="Contact" name="Contact" required><br>

            <label for="Email">Email:</label>
            <input type="email" id="Email" name="Email" required><br>

            <label for="Password">Password:</label>
            <input type="password" id="Password" name="Password" required><br>

            <label for="Joined_Date">Joined Date:</label>
            <input type="date" id="Joined_Date" name="Joined_Date" required><br>

            <input type="submit" value="Register">
        </form>
    </div>
</body>
</html>