<?php
session_start();
$loggedIn = isset($_SESSION['email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home - Movie Booking</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #1e1e2d;
      color: white;
    }

    /* Navbar */
    .navbar {
      background: #222;
      padding: 15px 5%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .navbar a {
      color: white;
      text-decoration: none;
      padding: 10px 15px;
      font-weight: bold;
    }

    .navbar a:hover {
      background-color: #ff4500;
      border-radius: 5px;
    }

    /* Banner section */
    .banner {
      position: relative;
      background: url('images/banner.jpg') no-repeat center center/cover;
      height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
    }

    .banner::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.2);
      z-index: 1;
    }

    .banner-content {
      position: relative;
      z-index: 2;
      max-width: 800px;
      padding: 0 20px;
    }

    .banner h1 {
      font-size: 48px;
      margin-bottom: 10px;
      color: #fff;
      text-shadow: 2px 2px 8px black;
    }

    .banner p {
      font-size: 20px;
      color: #ddd;
    }

    .btn {
      display: inline-block;
      margin-top: 20px;
      padding: 12px 25px;
      font-size: 18px;
      background: #ff4500;
      border: none;
      border-radius: 6px;
      color: white;
      text-decoration: none;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #e63e00;
    }

    .why-choose {
      margin-top: 50px;
      color: #fff;
    }

    .why-choose h2 {
      font-size: 28px;
      margin-bottom: 15px;
      border-bottom: 2px solid #ff4500;
      display: inline-block;
      padding-bottom: 5px;
    }

    .why-choose p {
      font-size: 18px;
      color: #eee;
    }

    /* Footer */
    .footer {
      background-color: #222;
      padding: 20px;
      text-align: center;
      color: #aaa;
    }

    /* Profile dropdown */
    .profile-menu {
      position: relative;
      display: inline-block;
    }

    .profile-menu > a {
      background-color: #444;
      padding: 10px 15px;
      border-radius: 5px;
    }

    .profile-menu:hover .profile-content {
      display: block;
    }

    .profile-content {
      display: none;
      position: absolute;
      right: 0;
      background: #333;
      min-width: 180px;
      border-radius: 5px;
      margin-top: 8px;
      z-index: 1000;
    }

    .profile-content a {
      color: white;
      padding: 12px 16px;
      display: block;
      text-decoration: none;
    }

    .profile-content a:hover {
      background-color: #444;
    }

    @media (max-width: 768px) {
      .banner h1 { font-size: 32px; }
      .banner p { font-size: 16px; }
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
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

  <!-- BANNER -->
  <div class="banner">
    <div class="banner-content">
      <h1>🎥 Welcome to Movie Booking</h1>
      <p>Book your favorite Telugu & Kannada movies in 2D, 3D, or IMAX format!</p>
      <a href="movies.php" class="btn">Book Tickets Now →</a>

      <!-- WHY CHOOSE US inside the banner -->
      <div class="why-choose">
        <h2>Why Choose Us?</h2>
        <p>✔ Easy booking &nbsp; | &nbsp; Seat selection like BookMyShow &nbsp; | &nbsp; Recent movies &nbsp; | &nbsp; Smooth UI</p>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="footer">
    <p>&copy; 2025 Movie Booking. All Rights Reserved.</p>
<p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>

  </div>

  <script>
    document.addEventListener("keydown", function(event) {
      if (event.ctrlKey && event.shiftKey && event.key === "A") {
        window.location.href = "admin/admin_login.php";
      }
    });
  </script>

</body>
</html>

