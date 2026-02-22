<?php
session_start();
$loggedIn = isset($_SESSION['email']);
include 'db.php';

$popupMessage = '';
$redirectToLogin = false;

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email exists
    $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();
    if ($check_email->num_rows > 0) {
        $popupMessage = "Email already registered. Try logging in.";
    }
    $check_email->close();

    // Check if phone exists
    $check_phone = $conn->prepare("SELECT phone FROM users WHERE phone = ?");
    $check_phone->bind_param("s", $phone);
    $check_phone->execute();
    $check_phone->store_result();
    if (empty($popupMessage) && $check_phone->num_rows > 0 ) {
        $popupMessage = "Mobile number already registered.";
    }
    $check_phone->close();

    // Validate password
    if (empty($popupMessage) && !preg_match('/^\d{6,}$/', $password)) {
        $popupMessage = "Password must be numeric and at least 6 digits long!";
    }

    // Register user if all checks pass
    if (empty($popupMessage)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, phone, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $phone, $email, $hashed_password);

        if ($stmt->execute()) {
            $popupMessage = "Registration successful! Redirecting to login...";
            $redirectToLogin = true;
        } else {
            $popupMessage = "Registration failed. Please try again.";
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('images/banner.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar {
            background: rgba(20, 20, 20, 0.95);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            width: 90%;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(255, 69, 0, 0.3);
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            font-weight: bold;
            transition: 0.3s;
        }
        .navbar a:hover {
            background: #ff1a1a;
            border-radius: 5px;
        }
       .login-form {
    max-width: 400px; /* changed from 400px */
    margin: 160px auto 30px auto;
    padding: 20px;
    background: rgba(0, 0, 0, 0.6);
    border-radius: 10px;
    box-shadow: 0px 5px 15px rgba(255, 255, 255, 0.1);
    text-align: center;
}
        .login-form h2 {
            color: #ff1a1a;
            margin-bottom: 25px;
        }
        input[type="email"],
        input[type="password"],
        input[type="text"],
        input[type="tel"] {
            width: 90%;
            padding: 12px;
            margin: 12px auto;
            display: block;
            background: #2b2b2b;
            border: none;
            border-radius: 5px;
            color: white;
        }
        input:focus {
            outline: none;
            background: #333;
            border: 1px solid #ff1a1a;
        }
        button {
            width: 95%;
            padding: 12px;
            background: #ff1a1a;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #e60000;
        }
        .footer {
            background-color: #222;
            padding: 20px;
            text-align: center;
            color: #aaa;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <?php if (!empty($popupMessage)): ?>
        <script>
            alert("<?= $popupMessage ?>");
            <?php if ($redirectToLogin): ?>
                window.location.href = "login.php";
            <?php endif; ?>
        </script>
    <?php endif; ?>

    <div class="navbar">
        <div>
            <a href="index.php">Home</a>
            <a href="about.php">About Us</a>
            <a href="contact.php">Contact Us</a>
            <a href="movies.php">Movies</a>
        </div>
    </div>

    <div class="login-form">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <input type="text" name="username" placeholder="Username" 
       pattern="[A-Za-z ]+" required 
       title="Username should contain letters only"
       oninput="this.value = this.value.replace(/[^A-Za-z ]/g, '')">
            <input type="tel" name="phone" placeholder="Mobile Number" 
       pattern="\d{10}" maxlength="10" minlength="10" required 
       title="Mobile number must be exactly 10 digits"
       oninvalid="this.setCustomValidity('Mobile number must be exactly 10 digits')"
       oninput="this.setCustomValidity(''); this.value = this.value.replace(/\D/g, '')">    
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Numeric Password (min 6 digits)" pattern="\d{6,}" required>
            <button type="submit" name="register">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <div class="footer">
        <p>&copy; 2025 Movie Booking. All Rights Reserved.</p>
    </div>
</body>
</html>
