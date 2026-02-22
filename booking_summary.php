<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $movie_id = $_POST['movie_id'] ?? null;
    $theater_id = $_POST['theater_id'] ?? null;
    $show_date = $_POST['show_date'] ?? null;
    $show_id = $_POST['show_id'] ?? null;
    $show_time = $_POST['show_time'] ?? null;
    $seats = $_POST['seats'] ?? null;
    $total_price = $_POST['total_price'] ?? 0;
    $language = $_POST['language'] ?? "Unknown";

    if (!$movie_id || !$theater_id || !$show_date || !$show_id || !$show_time || !$seats) {
        die("❌ Missing booking details. Please try again.");
    }
    // Calculate GST (18% of total price)
    $gst = round($total_price * 0.18, 2);
    $final_price = $total_price + $gst;

    // Fix SQL Queries with correct column names
    $sql = "SELECT name FROM movies WHERE movie_id = ?";  // Use movie_id instead of id
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie_name = $result->fetch_assoc()['name'] ?? "Unknown";
    $stmt->close();

    $sql = "SELECT name FROM theaters WHERE theater_id = ?";  // Use theater_id instead of id
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $theater_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $theater_name = $result->fetch_assoc()['name'] ?? "Unknown";
    $stmt->close();
    $conn->close();
} else {
    die("❌ Invalid request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Summary</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* General Reset and Body Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: url('images/bs.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
            text-align: center;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Full height */
        }

        /* Container Styling */
        .container {
            width: 70%;
            margin: auto;
            background: transparent;
            color: black;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(255, 255, 255, 0.1);
            flex: 1; /* Ensures it takes up available space */
        }

        h2 {
            color: #ffcc00;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #444;
            text-align: left;
        }

        th {
            background: #ff4500;
            color: black;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            background: #28a745;
            padding: 10px 20px;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
        }

        /* Footer Styling */
        footer {
            padding: 20px;
            background-color: #222;
            color: white;
            text-align: center;
            font-size: 0.9rem;
            width: 100%; /* Full width */
        }

        footer a {
            color: #dd2476;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
</style>
</head>
<body>
    <div class="container">
        <h2>Booking Summary</h2>
        <table>
          <tr><th>Movie</th><td><?= htmlspecialchars($movie_name) ?> (<?= htmlspecialchars($language) ?>)</td></tr>

            <tr><th>Theater</th><td><?= htmlspecialchars($theater_name) ?></td></tr>
            <tr><th>Date</th><td><?= htmlspecialchars($show_date) ?></td></tr>
            <tr><th>Show ID</th><td><?= htmlspecialchars($show_id) ?></td></tr>

            <tr><th>Show Time</th><td><?= htmlspecialchars($show_time) ?></td></tr>
            <tr><th>Seats</th><td><?= htmlspecialchars($seats) ?></td></tr>
            <tr><th>Ticket Price</th><td>₹<?= number_format($total_price, 2) ?></td></tr>
            <tr><th>GST (18%)</th><td>₹<?= number_format($gst, 2) ?></td></tr>
            <tr class="total"><th>Total Payable</th><td>₹<?= number_format($final_price, 2) ?></td></tr>
        </table>

     <a href="payment.php?movie_id=<?= $movie_id ?>&theater_id=<?= $theater_id ?>&show_date=<?= $show_date ?>&show_id=<?= $show_id ?>&show_time=<?= $show_time ?>&seats=<?= urlencode($seats) ?>&amount=<?= $final_price ?>&language=<?= urlencode($language) ?>" class="btn">Proceed to Payment</a>

    
    
    </div>
<footer>
    <p>&copy; 2025 Your Company Name. All rights reserved.</p>
    <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
</footer>
</body>
</html>
