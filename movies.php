<?php
include 'db.php';
session_start(); 
$loggedIn = isset($_SESSION['user_id']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Movies</title>
  <style>
   body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: url('images/movie.jpg') no-repeat center center fixed; /* Add your image URL here */
    background-size: cover; /* Ensures the image covers the entire viewport */
    color: white;
}

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
      transition: background 0.3s;
    }

    .navbar a:hover {
      background-color: #ff4500;
      border-radius: 5px;
    }

    .banner {
      background: url('images/m.jpg') no-repeat center center;
      background-size: cover;
      height: auto;
      min-height: 100vh;
      padding: 60px 20px;
      position: relative;
    }

    .banner::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.2); /* Just a slight overlay */
      z-index: 1;
    }

    .content {
      position: relative;
      z-index: 2;
      max-width: 1300px;
      margin: 0 auto;
      padding-top: 20px;
      text-align: center;
    }

    h1 {
      font-size: 48px;
      color: #fff;
      margin-bottom: 10px;
      text-shadow: 2px 2px 8px black;
    }

    h2 {
      color: #ff4500;
      margin-top: 60px;
      margin-bottom: 10px;
      font-size: 32px;
      text-shadow: 1px 1px 5px black;
    }

.movies {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); /* Responsive, 5 per row on big screens */
    gap: 25px;
    justify-content: center;
    padding: 30px;
    width: 100%;
    max-width: 1300px; /* Limits max width for better alignment */
    margin: auto;
}

/* Movie Card */
.movie {
    background: #36454F;
    border-radius: 12px;
    width: 220px;
    padding: 12px;
    transition: all 0.3s ease-in-out;
    box-shadow: 0px 4px 14px rgba(100, 149, 237, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.08);
    overflow: hidden;
    text-align: center;
}

.movie:hover {
    transform: scale(1.02);
    box-shadow: 0px 6px 16px rgba(255, 255, 255, 0.3);
}

/* Movie Poster */
.movie img {
    width: 100%;
    height: 320px; /* Slightly bigger for better visuals */
    object-fit: cover;
    border-radius: 12px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

/* Movie Title */
.movie h3 {
    margin-top: 12px;
    font-size: 16px;
    font-weight: bold;
    color: #f8f8f8;
    text-transform: capitalize;
}

/* Genre */
.genre {
    font-size: 14px;
    color: #ffcc00;
    margin-top: 4px;
}

/* 🎬 Now Showing Section */
.now-showing h2 {
    color: #ff5722;
    font-size: 24px;
    text-shadow: 0 0 10px rgba(255, 87, 34, 0.4);
}

/* 🚀 Upcoming Movies Section */
.upcoming-movies h2 {
    color: #ffcc00;
    font-size: 24px;
    text-shadow: 0 0 10px rgba(255, 204, 0, 0.4);
}
    .footer {
      background: #222;
      padding: 20px;
      text-align: center;
      color: #aaa;
    }

    .profile-menu {
      position: relative;
      display: inline-block;
    }

    .profile-menu > a {
      background-color: #444;
      padding: 10px 15px;
      border-radius: 5px;
      font-weight: bold;
    }

    .profile-content {
      display: none;
      position: absolute;
      right: 0;
      background-color: #333;
      min-width: 180px;
      border-radius: 5px;
      margin-top: 8px;
      box-shadow: 0px 8px 16px rgba(0,0,0,0.3);
      z-index: 100;
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

    .profile-menu:hover .profile-content {
      display: block;
    }

    @media (max-width: 768px) {
      h1 { font-size: 32px; }
      h2 { font-size: 24px; }
      .movie { width: 45%; }
    }

    @media (max-width: 480px) {
      .movie { width: 90%; }
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

  <div class="banner">
    <div class="content">
      <h1>🎬 All Movies 🎟️</h1>

      <!-- Released Movies -->
      <h2>Now Showing 🍿</h2>
      <div class="movies">
        <?php
        $released_query = "SELECT * FROM movies WHERE status = 'Released'";
        $released_result = $conn->query($released_query);
        if ($released_result->num_rows > 0) {
          while ($row = $released_result->fetch_assoc()) {
        ?>
            <div class="movie">
              <a href="movie_details.php?movie_id=<?php echo $row['movie_id']; ?>">
                <img src="images/<?php echo htmlspecialchars($row['poster'] ?: 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
              </a>
              <h3><?php echo htmlspecialchars($row['name']); ?></h3>
              <p class="genre">Genre: <?php echo htmlspecialchars($row['genre']); ?></p>
            </div>
        <?php
          }
        } else {
          echo "<p>No released movies available.</p>";
        }
        ?>
      </div>

      <!-- Upcoming Movies -->
      <h2>Coming Soon ⏳</h2>
      <div class="movies">
        <?php
        $upcoming_query = "SELECT * FROM movies WHERE status = 'Upcoming'";
        $upcoming_result = $conn->query($upcoming_query);
        if ($upcoming_result->num_rows > 0) {
          while ($row = $upcoming_result->fetch_assoc()) {
        ?>
            <div class="movie">
              <a href="movie_details.php?movie_id=<?php echo $row['movie_id']; ?>">
                <img src="images/<?php echo htmlspecialchars($row['poster'] ?: 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
              </a>
              <h3><?php echo htmlspecialchars($row['name']); ?></h3>
              <p class="genre">Genre: <?php echo htmlspecialchars($row['genre']); ?></p>
            </div>
        <?php
          }
        } else {
          echo "<p>No upcoming movies available.</p>";
        }
        ?>
      </div>
    </div>
  </div>

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
