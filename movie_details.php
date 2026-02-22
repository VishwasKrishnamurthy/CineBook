<?php
include 'db.php';
session_start();

if (!isset($_GET['movie_id']) || empty($_GET['movie_id'])) {
    die("<h2 style='color: red;'>Movie ID is missing!</h2>");
}


$movie_id = $_GET['movie_id'];

// Fetch movie details
$sql = "SELECT name, description, trailer_link, cast, languages,  status FROM movies WHERE movie_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<h2 style='color: red;'>Movie details not found!</h2>");
}

$movie = $result->fetch_assoc();
$stmt->close();

// Fetch available languages from showtimes table
$langQuery = "SELECT DISTINCT language FROM showtimes WHERE movie_id = ?";
$stmt = $conn->prepare($langQuery);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$langResult = $stmt->get_result();

$languages = [];
while ($row = $langResult->fetch_assoc()) {
    $languages[] = $row['language'];
}
$stmt->close();
$conn->close();

$loggedIn = isset($_SESSION['email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie['name']); ?> - Movie Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
      body {
    font-family: 'Poppins', sans-serif;
    background: url('images/md.jpg') no-repeat center center;
    background-size: cover;
    color: white;
    text-align: center;
    margin: 0;
    padding: 0;
    background-attachment: scroll; /* Ensures the image scrolls with the page */
}


        .navbar {
            background: #222;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            width: 90%;
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
       .container {
    background: transparent;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 5px 15px rgba(255, 255, 255, 0.1);
    margin-top: 80px;
    width: 80%;
    margin-left: auto;
    margin-right: auto;
}
        h1 { color: #ff4500; }
        p { font-size: 16px; line-height: 1.5; color: #ccc; }
        iframe { 
            width: 100%; 
            max-width: 800px; 
            height: 450px; 
            border-radius: 10px; 
            margin-top: 20px;
        }
        .lang-buttons { margin-top: 20px; }
        .lang-btn { background: #FFD700; color:black;border: none; padding: 10px 15px; border-radius: 5px; margin: 5px; cursor: pointer; transition: 0.3s; }
        .lang-btn:hover { background: #1DB954; }
        .not-released { color: red; font-size: 18px; margin-top: 20px; }
 .movie-details { margin-top: 15px; font-size: 16px; }
        .movie-details strong { color: #ff4500; }
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
    <h1><?php echo htmlspecialchars($movie['name']); ?></h1>
    <p><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
    <div class="movie-details">
          <p><strong>Languages:</strong> <?php echo htmlspecialchars($movie['languages']); ?></p>
        <p><strong>Cast:</strong> <?php echo htmlspecialchars($movie['cast']); ?></p>
    </div>

    <?php if (!empty($movie['trailer_link'])): ?>
        <iframe src="<?php echo htmlspecialchars($movie['trailer_link']); ?>" allowfullscreen></iframe>
    <?php else: ?>
        <p style="color: red;">No trailer available.</p>
    <?php endif; ?>

    <?php if (strtolower($movie['status']) === 'released'): ?>
        <h2>Available Languages</h2>
        <div class="lang-buttons">
            <?php foreach ($languages as $lang): ?>
                <form action="select_theater.php" method="GET" style="display:inline;">
                    <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                    <input type="hidden" name="language" value="<?php echo htmlspecialchars($lang); ?>">
                    <button type="submit" class="lang-btn"><?php echo htmlspecialchars($lang); ?></button>
                </form>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="not-released">This movie is coming soon, and booking will be available shortly</p>
    <?php endif; ?>
</div>

</body>
</html>
