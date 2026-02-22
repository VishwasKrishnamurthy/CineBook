<?php
include 'db.php';
session_start();
$loggedIn = isset($_SESSION['email']);

if (!$loggedIn) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h2 style='color: red;'>User not found!</h2>";
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Handle Form Submission
$success = $error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $new_phone = htmlspecialchars($_POST['phone']);

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        $updateSQL = "UPDATE users SET email = ?, phone = ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateSQL);
        $updateStmt->bind_param("sss", $new_email, $new_phone, $email);

        if ($updateStmt->execute()) {
            $_SESSION['email'] = $new_email;
            $success = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $error = "Failed to update profile. Try again!";
        }
        $updateStmt->close();
    }
}

$conn->close();
$name = htmlspecialchars($user['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: url('images/m.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
            text-align: center;
        }
        .navbar {
            background: #222;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            transition: 0.3s;
            font-weight: bold;
        }
        .navbar a:hover {
            background: #ff4500;
            border-radius: 5px;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(255, 255, 255, 0.1);
        }
        h1 { color: #ff4500; }
        p { font-size: 16px; line-height: 1.5; color: #ccc; }

        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #ff4500;
            margin: 0 auto 10px;
            overflow: hidden;
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn {
            margin-top: 15px;
            background: #ff4500;
            padding: 12px 18px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover { background: darkorange; }

        .edit-form {
            display: none;
            margin-top: 20px;
            text-align: left;
        }
        .edit-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: none;
            font-size: 14px;
        }
        .message {
            margin-top: 10px;
            font-size: 14px;
        }
        .error { color: red; }
        .success { color: limegreen; }

        .profile-menu {
            position: relative;
            display: inline-block;
        }
        .profile-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #222;
            min-width: 150px;
            box-shadow: 0px 4px 8px rgba(255, 255, 255, 0.2);
            z-index: 1;
        }
        .profile-content a {
            color: white;
            padding: 12px 16px;
            display: block;
        }
        .profile-menu:hover .profile-content {
            display: block;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div>
        <a href="index.php">Home</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
        <a href="movies.php">Movies</a>
    </div>
    <div>
        <div class="profile-menu">
            <a href="#">👤 My Account ▼</a>
            <div class="profile-content">
                <a href="profile.php">Profile</a>
                <a href="my_tickets.php">My Tickets 🎟️</a>
                <a href="logout.php">Logout 🚪</a>
            </div>
        </div>
    </div>
</div>

<!-- Profile Section -->
<div class="container">
    <div class="avatar">
        <img src="images/avatar.jpg" alt="Avatar">
    </div>
    <h1><?php echo $name; ?></h1>

    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>

    <button class="btn" onclick="toggleEdit()">Edit</button>

    <form class="edit-form" method="POST">
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        <button type="submit" class="btn">💾 Save Changes</button>
    </form>

    <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

    <a href="logout.php" class="btn">Logout</a>
</div>

<script>
    function toggleEdit() {
        var form = document.querySelector('.edit-form');
        form.style.display = form.style.display === 'block' ? 'none' : 'block';
    }
</script>

</body>
</html>

