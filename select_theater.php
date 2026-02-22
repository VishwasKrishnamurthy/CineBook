<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php?redirect=select_theater.php&movie_id=" . $_GET['movie_id'] . "&language=" . urlencode($_GET['language']));
    exit();
}

// Get movie ID and language
if (!isset($_GET['movie_id']) || !isset($_GET['language'])) {
    die("Movie ID or Language is missing.");
}

$movie_id = $_GET['movie_id'];
$selected_language = $_GET['language'];

// Fetch movie name using movie_id
$movieQuery = "SELECT name FROM movies WHERE movie_id = ?";
$stmt = $conn->prepare($movieQuery);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movieResult = $stmt->get_result();
$movie = $movieResult->fetch_assoc();
$stmt->close();
$movie_name = $movie ? $movie['name'] : "Unknown Movie";

// Fetch available cities
$cityQuery = "SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(address, ',', -2), ',', 1) AS city FROM theaters";
$cityResult = $conn->query($cityQuery);
$cities = [];
while ($row = $cityResult->fetch_assoc()) {
    $cities[] = trim($row['city']);
}

// Fetch available show dates for this movie
$dateQuery = "SELECT DISTINCT show_date FROM showtimes WHERE movie_id = ? ORDER BY show_date ASC";
$stmt = $conn->prepare($dateQuery);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$dateResult = $stmt->get_result();
$showDates = [];
while ($row = $dateResult->fetch_assoc()) {
    $showDates[] = $row['show_date'];
}
$stmt->close();

// Apply filters (City & Show Date)
$cityFilter = isset($_GET['city']) ? $_GET['city'] : '';
$showDateFilter = isset($_GET['show_date']) ? $_GET['show_date'] : '';

// Fetch theaters that have the selected movie in the selected language, city, and date
$sql = "SELECT t.theater_id, t.name AS theater_name, t.address, 
               s.show_id, s.show_date, s.show_time
        FROM theaters t
        JOIN showtimes s ON t.theater_id = s.theater_id
        WHERE s.movie_id = ? AND s.language = ?";

$params = [$movie_id, $selected_language];
$types = "is";

if ($cityFilter) {
    $sql .= " AND t.address LIKE ?";
    $params[] = "%" . $cityFilter . "%";
    $types .= "s";
}

if ($showDateFilter) {
    $sql .= " AND s.show_date = ?";
    $params[] = $showDateFilter;
    $types .= "s";
}

$sql .= " ORDER BY s.show_date, s.show_time";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$theaters = [];
while ($row = $result->fetch_assoc()) {
    $theaters[$row['theater_name']][] = $row; // Group by theater name
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Theater & Show</title>
    <link rel="stylesheet" href="styles.css">
    <style>
     body {
    font-family: 'Poppins', sans-serif;
    background: url('images/st.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #FFFFFF;
    text-align: center;
 min-height: 100vh;
    display: flex;
    flex-direction: column;
    margin: 0;
  background-attachment: scroll; 
}

.container {
    width: 80%;
    margin: 70px auto;
    background: transparent ;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
  flex: 1;
}

.filters {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 25px;
}

select {
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    background: rgba(40, 40, 40, 0.9);
    color: #FFFFFF;
}

.theater {
    background: rgba(30, 30, 30, 0.9);
    padding: 20px;
    margin: 15px 0;
    border-radius: 15px;
    text-align: left;
    border-left: 5px solid #FFD700;
}

.theater h3 {
    color: #FFD700;
    font-weight: bold;
}

.theater p {
    color: #CCCCCC;
    margin-top: 5px;
}

.showtimes {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 12px;
}

.showtime-btn {
    background: #00C853;
    color: #FFFFFF;
    padding: 10px 18px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: background 0.3s ease;
}

.showtime-btn:hover {
    background: #00A043;
}

h2 {
    font-size: 2.2rem;
    margin-bottom: 30px;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
}


.footer {
    text-align: center;
 background: #222;
    padding: 15px;
    color: #aaaaaa;
    font-size: 14px;
}

    </style>
</head>
<body>
    <div class="container">
       
 <h1>Select Theater & Show Time</h1>
       

        <!-- Filter Form -->
        <form method="GET" action="select_theater.php" class="filters">
            <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
            <input type="hidden" name="language" value="<?php echo htmlspecialchars($selected_language); ?>">

            <select name="city">
                <option value="">Select City</option>
                <?php foreach ($cities as $city): ?>
                    <option value="<?php echo htmlspecialchars($city); ?>" 
                        <?php echo ($city == $cityFilter) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($city); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="show_date">
                <option value="">Select Show Date</option>
                <?php foreach ($showDates as $date): ?>
                    <option value="<?php echo htmlspecialchars($date); ?>" 
                        <?php echo ($date == $showDateFilter) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($date); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="filter-btn">Apply Filters</button>
        </form>

        <!-- Theater Listings -->
        <?php if (empty($theaters)): ?>
            <p>No shows available for this movie in <?php echo htmlspecialchars($selected_language); ?>.</p>
        <?php else: ?>
            <?php foreach ($theaters as $theater_name => $shows): ?>
                <div class="theater">
                    <h3><?php echo htmlspecialchars($theater_name); ?></h3>
                    <p><?php echo htmlspecialchars($shows[0]['address']); ?></p>
                    <div class="showtimes">
                        <?php foreach ($shows as $show): ?>
                          <form action="seat_selection.php" method="POST">
    <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
    <input type="hidden" name="theater_id" value="<?= $show['theater_id'] ?>">
    <input type="hidden" name="show_id" value="<?= $show['show_id'] ?>">
    <input type="hidden" name="show_date" value="<?= $show['show_date'] ?>">
    <input type="hidden" name="show_time" value="<?= $show['show_time'] ?>">
    <button type="submit" class="showtime-btn">
        <?= htmlspecialchars($show['show_date']) ?> - <?= htmlspecialchars($show['show_time']) ?>
    </button>
</form>

                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<div class="footer">
    © 2025 MovieBooking. All rights reserved.
</div>

</body>
</html>
