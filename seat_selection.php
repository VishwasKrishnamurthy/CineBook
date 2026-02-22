<?php
session_start();

include 'db.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
$movie_id = $_POST['movie_id'] ?? null;
$theater_id = $_POST['theater_id'] ?? null;
$show_date = $_POST['show_date'] ?? null;
$show_id = $_POST['show_id'] ?? null;
$show_time = $_POST['show_time'] ?? null;

if (!$movie_id || !$theater_id || !$show_date || !$show_id || !$show_time) {
    die("❌ Missing required parameters.");
}
$sql = "SELECT m.name AS movie_name, t.name AS theater_name, s.language,  
               DATE_FORMAT(s.show_date, '%W, %M %e, %Y') AS formatted_date
        FROM movies m 
        JOIN showtimes s ON m.movie_id = s.movie_id 
        JOIN theaters t ON s.theater_id = t.theater_id
        WHERE s.theater_id = ? AND m.movie_id = ? AND s.show_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $theater_id, $movie_id, $show_id); // ✅ Corrected number of parameters
$stmt->execute();
$result = $stmt->get_result();
$movie_info = $result->fetch_assoc();

$movie_name = $movie_info['movie_name'] ?? "Unknown Movie";
$theater_name = $movie_info['theater_name'] ?? "Unknown Theater";
$language = $movie_info['language'] ?? "Unknown Language";
$formatted_date = $movie_info['formatted_date'] ?? "Unknown Date";

$stmt->close();

// Fetch total seats and ticket prices from theaters and showtimes tables
$sql = "SELECT t.total_seats, s.silver_price, s.gold_price, s.platinum_price 
        FROM theaters t 
        JOIN showtimes s ON t.theater_id = t.theater_id 
        WHERE t.theater_id = ? AND s.show_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $theater_id, $show_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_seats = $row['total_seats'] ?? 200; // Default to 200 if not found
$silver_price = $row['silver_price'] ?? 100;
$gold_price = $row['gold_price'] ?? 150;
$premium_price = $row['platinum_price'] ?? 200;


$stmt->close();

// Calculate seat distribution
$silver_seats = round($total_seats * 0.50);
$gold_seats = round($total_seats * 0.30);
$premium_seats = $total_seats - ($silver_seats + $gold_seats);

$rows = range('A', 'J'); 
$seats_per_row = ceil($total_seats / count($rows));

// Fetch already booked seats
$booked_seats = [];
$sql = "SELECT seat_numbers FROM bookings WHERE movie_id = ? AND theater_id = ? AND show_date = ? AND show_id = ? AND show_time = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisss", $movie_id, $theater_id, $show_date, $show_id, $show_time);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $booked_seats = array_merge($booked_seats, explode(",", $row['seat_numbers']));
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seat Selection</title>
    <link rel="stylesheet" href="styles.css">
    <style>
       body {
    font-family: Arial, sans-serif;
    background: url('images/sess.jpg') no-repeat center center fixed;
    background-size: cover;
    color: white;
    text-align: center;
    margin: 0;
    padding: 0;
}

        .container { width: 90%; margin: auto; background: #2a2a40; padding: 20px; border-radius: 10px; box-shadow: 0px 5px 15px rgba(255, 255, 255, 0.1); }
        .screen { background: silver; padding: 15px; margin-bottom: 20px; color: black; font-weight: bold; border-radius: 10px; width: 60%; margin: auto; font-size: 1.2em; }
        .seating { display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .row { display: flex; justify-content: center; gap: 5px; }
        .seat { width: 40px; height: 40px; text-align: center; line-height: 40px; font-size: 14px; border-radius: 5px; cursor: pointer; }
        .silver { background: silver; color: white; }
        .gold { background: #007bff; color: white; }
        .premium { background: #ff6347; color: white; }
        .booked { background: #555 !important; color: #aaa; pointer-events: none; }
        .selected { background: #32CD32 !important; }
        
        .gap { width: 15px; height: 40px; }
        .row-gap { height: 20px; }
        .book-btn { background: linear-gradient(135deg, #ff416c, #ff4b2b); color: white; font-size: 18px; font-weight: bold; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; transition: all 0.3s ease-in-out; box-shadow: 0px 4px 10px rgba(255, 75, 43, 0.3); width: 100%; max-width: 300px; }
        .book-btn:hover { background: linear-gradient(135deg, #ff4b2b, #ff416c); box-shadow: 0px 6px 15px rgba(255, 75, 43, 0.5); }
        .book-btn:disabled { background: #444; cursor: not-allowed; box-shadow: none; }
.movie-details {
    display: flex;
    justify-content: center;
    align-items: center;
    background: transparent ;    padding: 15px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.1);
    width: 90%;
    margin: 20px auto;
    color: white;
    text-align: center;
    font-size: 16px;
}

.movie-details .detail {
    margin: 0 20px;
    padding: 5px 15px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
}
 
.legend {
    display: flex;
    justify-content: center; /* Centers the legend items */
    gap: 20px; /* Adds space between each legend item */
    margin: 15px 0;
    font-size: 14px;
    background:transparent ;
    padding: 10px;
    border-radius: 8px;
}

.legend div {
    display: flex;
    align-items: center;
    gap: 5px;
}

.legend span {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 5px;
}

.silver { background: silver; }
.gold { background: #007bff; }
.premium { background: #ff6347; }
.booked { background: #555 !important; } /* Booked Seat */
.selected { background: #32CD32 !important; } /* Selected Seat */

    </style>
</head>
<body>
   <div class="movie-details">
    <div class="detail"><strong>🎬 Movie:</strong> <?= htmlspecialchars($movie_name); ?> (<?= htmlspecialchars($language); ?>)</div>
    <div class="detail"><strong>🏛 Theater:</strong> <?= htmlspecialchars($theater_name); ?></div>
    <div class="detail"><strong>📅 Date:</strong> <?= htmlspecialchars($formatted_date); ?></div>
</div>

        <h2>Choose Your Seats</h2>
        <div class="screen">SCREEN</div>
       
<div class="legend">
    <div><span class="legend-box silver"></span> Silver ₹<?= $silver_price; ?></div>
    <div><span class="legend-box gold"></span> Gold ₹<?= $gold_price; ?></div>
    <div><span class="legend-box premium"></span> Premium ₹<?= $premium_price; ?></div>
    <div><span class="legend-box booked"></span> Booked</div>
    <div><span class="legend-box selected"></span>Selected</div>
</div>

        <div class="seating">
            <?php
            $seat_counter = 0;
            foreach ($rows as $row) {
                echo "<div class='row'>";
                for ($i = 1; $i <= $seats_per_row; $i++) {
                    if ($seat_counter >= $total_seats) break;
                    
                    $seat_no = $row . $i;
                    $seat_class = ($seat_counter < $silver_seats) ? "silver" : (($seat_counter < $silver_seats + $gold_seats) ? "gold" : "premium");
                    $seat_price = ($seat_class == "silver") ? $silver_price : (($seat_class == "gold") ? $gold_price : $premium_price);
                    $booked_class = in_array($seat_no, $booked_seats) ? "booked" : "";
                    
                    echo "<div class='seat $seat_class $booked_class' data-seat='$seat_no' data-price='$seat_price'>$seat_no</div>";
                    $seat_counter++;

                    if ($i == 10) echo "<div class='gap'></div>"; 
                }
                echo "</div>";
                if ($seat_counter % 100 == 0) echo "<div class='row-gap'></div>";
            }
            ?>
        </div>
        <form id="seatForm" action="booking_summary.php" method="POST">
            <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
            <input type="hidden" name="theater_id" value="<?= $theater_id ?>">
            <input type="hidden" name="show_id" value="<?= $show_id ?>">
            <input type="hidden" name="show_date" value="<?= $show_date ?>">
            <input type="hidden" name="show_time" value="<?= $show_time ?>">
            <input type="hidden" name="seats" id="selectedSeats">
            <input type="hidden" name="total_price" id="totalPriceInput">
 <input type="hidden" name="language" value="<?= htmlspecialchars($language) ?>">


            <button type="submit" id="bookNow" class="book-btn" disabled>Pay ₹0</button>
        </form>
    </div>
</body>
</html>

    <script>
        let selectedSeats = [];
        let totalPrice = 0;

        document.querySelectorAll(".seat:not(.booked)").forEach(seat => {
            seat.addEventListener("click", function() {
                let seatNo = this.getAttribute("data-seat");
                let seatPrice = parseInt(this.getAttribute("data-price"));

                if (selectedSeats.includes(seatNo)) {
                    selectedSeats = selectedSeats.filter(s => s !== seatNo);
                    totalPrice -= seatPrice;
                    this.classList.remove("selected");
                } else {
                    selectedSeats.push(seatNo);
                    totalPrice += seatPrice;
                    this.classList.add("selected");
                }

                document.getElementById("selectedSeats").value = selectedSeats.join(",");
                document.getElementById("totalPriceInput").value = totalPrice;
                document.getElementById("bookNow").disabled = selectedSeats.length === 0;
                document.getElementById("bookNow").innerText = "Pay ₹" + totalPrice;
            });
        });
    </script>
</body>
</html>
