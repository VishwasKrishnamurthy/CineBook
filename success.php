<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    die("❌ Error: User not logged in.");
}
$loggedIn = isset($_SESSION['user_id']); 
$booking_id = $_GET['booking_id'] ?? null;

if (!$booking_id) {
    die("❌ Error: Invalid request.");
}

// Fetch booking details along with movie and theater names
$query = $conn->prepare("
    SELECT 
        b.booking_id, 
        m.name AS movie_name, 
        m.poster AS movie_poster,  -- Fetch poster directly
        t.name AS theater_name, 
        b.show_date, 
        s.show_time,
        s.language,  
        b.seat_numbers, 
        b.total_price,
        b.email AS user_email,
        p.payment_method,
        p.transaction_id
    FROM bookings b
    JOIN movies m ON b.movie_id = m.movie_id
    JOIN theaters t ON b.theater_id = t.theater_id
    JOIN showtimes s ON b.show_id = s.show_id
    JOIN payments p ON b.payment_id = p.payment_id
    WHERE b.booking_id = ?
");

$query->bind_param("i", $booking_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    $language = $booking['language'];  // ✅ Assign language here
} else {
    die("❌ Error: Booking not found.");
}
$query->close();
$conn->close();

// ✅ Fetch movie poster from database
$movie_poster = !empty($booking['movie_poster']) ? "images/" . $booking['movie_poster'] : "images/default_poster.jpg";

// ✅ Generate QR Code
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=BookingID_" . urlencode($booking['booking_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
   /* Body */
body {
    background: url('images/bs.jpg') no-repeat center center fixed, #1e1e2d; 
    background-size: cover;
    color: white;
    font-family: 'Poppins', sans-serif;
    text-align: center;
    padding: 0;
    margin: 0;
    min-height: 100vh; /* Ensures the body takes the full height */
    display: flex;
    flex-direction: column;
  background-attachment: scroll; 
}

/* Navbar */
.navbar {
    background: #222;
    padding: 15px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
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

/* Profile Menu */
.profile-menu {
    position: relative;
    display: inline-block;
    cursor: pointer; 
    right: 90px; /* Move profile menu slightly to the left */
}

.profile-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: #222;
    min-width: 150px;
    box-shadow: 0px 4px 8px rgba(255, 255, 255, 0.2);
    z-index: 1000;
    border-radius: 5px;
    overflow: hidden;
}

.profile-content a {
    color: white;
    padding: 12px 16px;
    display: block;
    text-decoration: none;
    transition: background 0.3s;
}

.profile-content a:hover {
    background-color: #333;
}

/* Show dropdown on hover */
.profile-menu:hover .profile-content {
    display: block;
}

/* Prevent content from hiding behind navbar */
body {
    padding-top: 80px; 
    flex-grow: 1; /* Ensures the content can expand */
}

/* Container */
.container {
    max-width: 800px;
    margin: 0 auto; /* Center the container horizontally */
    background: transparent;
    padding: 30px;
    color: black;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(255, 255, 255, 0.15);
    position: relative;
    z-index: 1; 
    flex-grow: 1; /* Allows container to grow and fill available space */
}

/* Movie Info */
.movie-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    text-align: left;
}

.movie-poster {
    width: 220px;
    height: auto;
    border-radius: 10px;
    box-shadow: 0px 0px 12px rgba(255, 255, 255, 0.3);
}

.details {
    flex: 1;
}

/* QR Code */
.qr-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 20px;
}

.qr-code {
    width: 150px;
    height: 150px;
    margin-top: 10px;
}

/* Buttons */
.btn-container {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

.btn {
    background: #ff5722;
    border: none;
    padding: 12px 18px;
    font-size: 1rem;
    color: white;
    cursor: pointer;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
}

.btn:hover {
    background: #e64a19;
}

/* Footer */
footer {
    background: #222;
    color: white;
    padding: 20px;
    position: relative;
    bottom: 0;
    width: 100%;
    text-align: center;
    font-size: 14px;
    margin-top: auto; /* Ensures footer stays at the bottom */
}

footer a {
    color: #ff5722;
    text-decoration: none;
}

footer a:hover {
    text-decoration: underline;
}



    @media print {
        body * {
            visibility: hidden; /* Hide everything by default */
        }

        .container, 
        .container * {
            visibility: visible; /* Show only the container */
        }

        .container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            background: #fdfdfd !important;
            color: #333 !important;
            border: 2px solid #ddd;
            box-shadow: none;
        }

        .movie-info {
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .movie-poster {
            width: 250px;
            height: auto;
            border: 2px solid #ccc;
        }

        .details p {
            font-size: 17px;
            font-weight: normal;
            color: #444;
        }

        .qr-container img {
            width: 140px;
            height: 140px;
            border: 1px solid #ccc;
        }

        .btn-container,
        .navbar {  
            display: none !important; /* Hide navbar & buttons */
        }
    }

    </style>
</head>
<body>

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

<div class="container">
    <h2>🎉 Booking Confirmed!</h2>

    <div class="movie-info">
        <img src="<?= htmlspecialchars($movie_poster) ?>" alt="Movie Poster" class="movie-poster">
      <div class="details">
    <p><strong>🎟 Booking ID:</strong> <?= htmlspecialchars($booking['booking_id']) ?></p>
    <p><strong>🎬 Movie:</strong> <?= htmlspecialchars($booking['movie_name']) ?> (<?= htmlspecialchars($language) ?>)</p>
    <p><strong>🏛 Theater:</strong> <?= htmlspecialchars($booking['theater_name']) ?></p>
    <p><strong>📅 Show Date:</strong> <?= htmlspecialchars($booking['show_date']) ?></p>
    <p><strong>⏰ Show Time:</strong> <?= htmlspecialchars($booking['show_time']) ?></p>
    <p><strong>💺 Seats:</strong> <?= htmlspecialchars($booking['seat_numbers']) ?></p>
    <p><strong>💰 Total Paid:</strong> ₹<?= number_format($booking['total_price'], 2) ?></p>
    <p><strong>💳 Payment Method:</strong> <?= htmlspecialchars($booking['payment_method']) ?></p>
    <p><strong>🔗 Transaction ID:</strong> <?= htmlspecialchars($booking['transaction_id']) ?> ✅</p>
    <p class="success-msg">🎉✅ Your booking has been confirmed. Enjoy your movie! 🍿🎬</p>
</div>

    </div>

    <div class="qr-container">
        <p>📌 Show this QR code at the entrance:</p>
        <img src="<?= htmlspecialchars($qr_code_url) ?>" alt="QR Code" class="qr-code">
    </div>

    <div class="btn-container">
        <button class="btn" onclick="window.print()">🖨 Print Ticket</button>
        <a href="index.php" class="btn">🏠 Home</a>
    </div>
</div>

<!-- Footer -->
<footer>
    <p>© 2025 Your Movie Booking Website | <a href="privacy.php">Privacy Policy</a> | <a href="terms.php">Terms & Conditions</a></p>
</footer>

</body>
</html>
