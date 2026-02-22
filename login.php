<?php
session_start();
include 'db.php';

$loggedIn = isset($_SESSION['email']);

if ($loggedIn && isset($_GET['redirect'])) {
    header("Location: " . $_GET['redirect']);
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['email'] = $email;
            $_SESSION['user_id'] = $user['user_id'];

            $redirect = 'index.php';
            if (isset($_GET['redirect'])) {
                $redirect = $_GET['redirect'];
                $params = [];
                if (isset($_GET['movie_id'])) $params['movie_id'] = $_GET['movie_id'];
                if (isset($_GET['language'])) $params['language'] = $_GET['language'];
                if (!empty($params)) $redirect .= '?' . http_build_query($params);
            }

            header("Location: $redirect");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Invalid email or password!";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Movie Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('images/banner.jpg') no-repeat center center fixed;
            background-size: cover;
 height: 100%;
 display: flex;
  flex-direction: column;
            margin: 0;
            padding: 0;
            color: white;
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

        .profile-menu {
            position: relative;
            display: inline-block;
        }

        .profile-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #1a1a1a;
            min-width: 150px;
            box-shadow: 0px 4px 8px rgba(255, 255, 255, 0.15);
            z-index: 1;
            border-radius: 5px;
        }

        .profile-content a {
            color: white;
            padding: 12px 16px;
            display: block;
        }

        .profile-content a:hover {
            background-color: #ff1a1a;
        }

        .profile-menu:hover .profile-content {
            display: block;
        }

        .login-form {
            max-width: 400px;
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
        input[type="password"] {
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
.page-wrapper {
  flex: 1;
}


        button:hover {
            background: #e60000;
        }

        .error {
            color: #ff4d4d;
            font-size: 14px;
            margin-top: 10px;
        }

        a {
            color: #ff8080;
            text-decoration: underline;
        }

        a:hover {
            color: #fff;
        }

        @media (max-width: 480px) {
            .login-form {
                width: 90%;
            }

            button, input {
                width: 100%;
            }

            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
.footer {
  background-color: #222;
  padding: 20px;
  text-align: center;
  color: #aaa;
}
html {
    height: 100%;
}

    </style>
</head>
<body>

 <div class="page-wrapper">

<div class="navbar">
    <div>
        <a href="index.php">Home</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
        <a href="movies.php">Movies</a>
    </div>
</div>

<div class="login-form">
    <h2>🎟 Login to Book Your Seat</h2>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="login.php<?php echo isset($_GET['redirect']) ? '?' . http_build_query($_GET) : ''; ?>" method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <p style="margin-top: 15px;">
        Don't have an account?
        <a href="register.php">Register here</a>
    </p>
</div>
</div>
<!-- FOOTER -->
<div class="footer">
  <p>&copy; 2025 Movie Booking. All Rights Reserved.</p>
<p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>

</div>


</body>
</html>
