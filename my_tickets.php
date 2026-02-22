<?php
session_start();
include 'db.php';

$loggedIn = isset($_SESSION['user_id']);
$user_id = $loggedIn ? $_SESSION['user_id'] : null;

if (!$loggedIn) {
    die("❌ Error: User not logged in.");
}

// Fetch email using user_id
$emailQuery = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
$emailQuery->bind_param("i", $user_id);
$emailQuery->execute();
$emailResult = $emailQuery->get_result();
$emailRow = $emailResult->fetch_assoc();
$emailQuery->close();

if (!$emailRow) {
    die("❌ Error: Email not found.");
}

$email = $emailRow['email']; // Get email from users table

// Fetch user bookings using email
$query = $conn->prepare("
    SELECT b.booking_id, m.name AS movie_name, s.language, t.name AS theater_name, 
           b.show_date, b.show_time, b.seat_numbers, b.total_price, b.booking_time
    FROM bookings b
    JOIN showtimes s ON b.show_id = s.show_id  -- Join with showtimes table
    JOIN movies m ON s.movie_id = m.movie_id  -- Join with movies table
    JOIN theaters t ON b.theater_id = t.theater_id
    WHERE b.email = ?
    ORDER BY b.booking_time DESC
");

$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets</title>
    <link rel="stylesheet" href="styles.css"> 
    <style>
    /* 🔹 Prevent Side Scrolling */
html, body {
    overflow-x: hidden;
    width: 100%;
    margin: 0;
    padding: 0;
}

/* 🔹 Global Styles */
body {
    background: #1e1e2d;
    color: white;
    font-family: 'Poppins', sans-serif;
    text-align: center;
    padding: 20px;
}

.navbar {
    background: #222;
    padding: 15px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0; /* ✅ Ensures it sticks to the very left */
    width: 100vw; /* ✅ Uses full viewport width */
    z-index: 1000;
    box-sizing: border-box;
}

/* 🔹 Navbar Links */
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

/* 🔹 Main Container */
.container {
    width: 90%;
    max-width: 800px;
    margin: 80px auto 20px auto; /* ✅ Added margin from top to avoid navbar overlap */
    background: #2a2a40; 
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(255, 255, 255, 0.15);
    box-sizing: border-box;
}

/* 🔹 Ticket Styling */
.ticket {
    border: 2px solid white;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    text-align: left;
    position: relative;
}

/* 🔹 QR Code */
.qr-code {
    position: absolute;
    right: 15px;
    top: 15px;
    width: 80px;
    height: 80px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    border-radius: 8px;
}

/* 🔹 Back Button */
.btn-back {
    display: inline-block;
    margin: 20px auto;
    padding: 6px 12px;
    background: linear-gradient(135deg, #00b4db, #0083b0); /* Blue to teal */
    color: white;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: linear-gradient(135deg, #0083b0, #00b4db);
    transform: scale(1.05);
}

/* 🔹 Profile Menu */
.profile-menu {
    position: relative;
    display: inline-block;
}

.profile-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: #222;
    min-width: 180px; /* ✅ Increased for better fit */
    box-shadow: 0px 4px 8px rgba(255, 255, 255, 0.2);
    z-index: 1000;
    border-radius: 5px;
    overflow: hidden;
}

.profile-content a {
    color: white;
    padding: 12px 16px;
    display: block;
    text-align: left;
    transition: background 0.3s;
}

.profile-content a:hover {
    background-color: #333;
}

.profile-menu:hover .profile-content {
    display: block;
}
html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
}

.wrapper {
    flex: 1;
}



    </style>
</head>
<body>
<div class="wrapper">

<!-- 🔹 Navigation Bar with Profile Dropdown -->
<div class="navbar">
    <div>
        <a href="index.php">Home</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
        <a href="movies.php">Movies</a>
    </div>
    <div>
        <?php if ($loggedIn): ?>
            <div class="profile-menu">
                <a href="#">👤 My Account ▼</a>
                <div class="profile-content">
                    <a href="profile.php">Profile</a>
                    <a href="my_tickets.php">My Tickets 🎟️</a>
                   
                    <a href="logout.php">Logout 🚪</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</div>

<h2 style="margin-top: 80px;">🎟 My Tickets</h2>

<div class="container">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="ticket">
                <p><strong>🎬 Movie:</strong> <?= htmlspecialchars($row['movie_name']) ?> (<?= htmlspecialchars($row['language']) ?>)</p>
                <p><strong>🏛 Theater:</strong> <?= htmlspecialchars($row['theater_name']) ?></p>
                <p><strong>📅 Date:</strong> <?= htmlspecialchars($row['show_date']) ?></p>
                <p><strong>⏰ Time:</strong> <?= htmlspecialchars($row['show_time']) ?></p>
                <p><strong>🎟 Seats:</strong> <?= htmlspecialchars($row['seat_numbers']) ?></p>
                <p><strong>💰 Total Paid:</strong> ₹<?= number_format($row['total_price'], 2) ?></p>
                <p><strong>🕒 Booked On:</strong> <?= htmlspecialchars($row['booking_time']) ?></p>
                
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode("Movie: {$row['movie_name']}, Theater: {$row['theater_name']}, Date: {$row['show_date']}, Time: {$row['show_time']}, Seats: {$row['seat_numbers']}") ?>" alt="QR Code">
                </div>
              
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No tickets found.</p>
    <?php endif; ?>
</div>

<a href="index.php" class="btn-back">🔙 Back to Home</a>
</div>
</body>

<footer style="background-color: #111; width: 100vw; padding: 10px 0; color: #ccc; text-align: center; font-size: 13px;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 10px;">
        <p style="margin-bottom: 5px;">&copy; <?php echo date("Y"); ?> Movie Ticket Booking System</p>
        <p style="margin-bottom: 5px;">
            Contact: 
            <a href="mailto:support@mtbs.com" style="color: #ff4500; text-decoration: none;">support@mtbs.com</a>
        </p>
        <p style="margin-bottom: 0;">
            Follow us: 
            <a href="#" style="color: #ff4500; text-decoration: none; margin: 0 5px;">Instagram</a> |
            <a href="#" style="color: #ff4500; text-decoration: none; margin: 0 5px;">Twitter</a> |
            <a href="#" style="color: #ff4500; text-decoration: none; margin: 0 5px;">Facebook</a>
        </p>
    </div>
</footer>



</html>
