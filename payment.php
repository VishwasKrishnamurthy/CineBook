<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("❌ Error: User not logged in.");
}

$user_id = $_SESSION['user_id'];
$movie_id = $_POST['movie_id'] ?? $_GET['movie_id'] ?? null;
$theater_id = $_POST['theater_id'] ?? $_GET['theater_id'] ?? null;
$show_id = $_POST['show_id'] ?? $_GET['show_id'] ?? null;
$show_date = $_POST['show_date'] ?? $_GET['show_date'] ?? null;
$show_time = $_POST['show_time'] ?? $_GET['show_time'] ?? null;
$selected_seats = $_POST['seats'] ?? $_GET['seats'] ?? null;
$total_price = $_POST['total_price'] ?? $_GET['amount'] ?? null;
$language = $_POST['language'] ?? $_GET['language'] ?? "Unknown";


if (!$movie_id || !$theater_id || !$show_date || !$show_time || !$selected_seats || !$total_price) {
    die("❌ Error: Missing required parameters.");
}

$movie_name = "Unknown Movie";
$movie_query = $conn->prepare("SELECT name FROM movies WHERE movie_id = ?");
$movie_query->bind_param("i", $movie_id);
$movie_query->execute();
$movie_query->bind_result($movie_name);
$movie_query->fetch();
$movie_query->close();

$theater_name = "Unknown Theater";
$theater_query = $conn->prepare("SELECT name FROM theaters WHERE theater_id = ?");
$theater_query->bind_param("i", $theater_id);
$theater_query->execute();
$theater_query->bind_result($theater_name);
$theater_query->fetch();
$theater_query->close();

// GST Calculation (18%)

$final_price = $total_price ;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Payment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #101026;
            font-family: 'Poppins', sans-serif;
            color: #fff;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: #1e1e2f;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.6);
        }

        .left, .right {
            padding: 40px;
        }

        .left {
            background: #292944;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .left h2 {
            margin-bottom: 30px;
            font-size: 24px;
            color: #ffcc00;
        }

        .details p {
            margin: 15px 0;
            font-size: 16px;
        }

        .right h2 {
            margin-bottom: 30px;
            font-size: 22px;
            color: #00f7ff;
        }

       .form-group {
    margin-bottom: 20px;
    width: 95%;
    box-sizing: border-box;
}

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
        }

        input, select {
            width: 95%;
            padding: 12px 15px;
            border-radius: 8px;
            border: none;
            background-color: #2c2c3e;
            color: #fff;
            font-size: 16px;
        }

        input:focus, select:focus {
            outline: none;
            box-shadow: 0 0 5px #00f7ff;
        }

        .hidden {
            display: none;
        }

        .btn-primary {
            width: 95%;
            padding: 14px;
            background: linear-gradient(135deg, #00f7ff, #007bff);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #007bff, #00f7ff);
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Left Side: Details -->
    <div class="left">
        <h2>🎟 Booking Summary</h2>
       <div class="details">
    <p><strong>🎬 Movie:</strong> <?= htmlspecialchars($movie_name) ?> <span style="color:#ffcc00;">(<?= htmlspecialchars($language) ?>)</span></p>

    <p><strong>🏢 Theater:</strong> <?= htmlspecialchars($theater_name) ?></p>
    <p><strong>📅 Date:</strong> <?= htmlspecialchars($show_date) ?></p>
    <p><strong>⏰ Time:</strong> <?= htmlspecialchars($show_time) ?></p>
    <p><strong>💺 Seats:</strong> <?= htmlspecialchars($selected_seats) ?></p>
    <p><strong>💰 Total:</strong> ₹<?= number_format($final_price, 2) ?></p>
</div>

    </div>

    <!-- Right Side: Payment -->
    <div class="right">
        <h2>💳 Choose Payment Method</h2>
        <form action="payment_process.php" method="POST">
            <!-- Hidden Inputs -->
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
            <input type="hidden" name="movie_id" value="<?= htmlspecialchars($movie_id) ?>">
            <input type="hidden" name="theater_id" value="<?= htmlspecialchars($theater_id) ?>">
            <input type="hidden" name="show_id" value="<?= htmlspecialchars($show_id) ?>">
            <input type="hidden" name="show_date" value="<?= htmlspecialchars($show_date) ?>">
            <input type="hidden" name="show_time" value="<?= htmlspecialchars($show_time) ?>">
            <input type="hidden" name="seats" value="<?= htmlspecialchars($selected_seats) ?>">
            <input type="hidden" name="total_price" value="<?= htmlspecialchars($final_price) ?>">
            <input type="hidden" name="language" value="<?= htmlspecialchars($language ?? 'Unknown') ?>">

            <div class="form-group">
                <label for="payment_method">Payment Method:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="">-- Select --</option>
                    <option value="UPI">UPI</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                </select>
            </div>

            <div id="upi_details" class="hidden">
                <div class="form-group">
                    <label for="upi_id">UPI ID:</label>
                    <input type="text" name="upi_id" id="upi_id" placeholder="example@upi">
                </div>
            </div>

            <div id="card_details" class="hidden">
                <div class="form-group">
                    <label for="card_number">Card Number:</label>
                    <input type="text" name="card_number" id="card_number" placeholder="**** **** **** ****">
                </div>
                <div class="form-group">
                    <label for="expiry_date">Expiry Date:</label>
                    <input type="month" name="expiry_date" id="expiry_date">
                </div>
                <div class="form-group">
                    <label for="cvv">CVV:</label>
                    <input type="text" name="cvv" id="cvv" placeholder="***">
                </div>
            </div>

            <button type="submit" class="btn-primary">Pay ₹<?= number_format($final_price, 2) ?></button>
        </form>
    </div>
</div>

<script>
    const paymentMethod = document.getElementById("payment_method");
    const upiDetails = document.getElementById("upi_details");
    const cardDetails = document.getElementById("card_details");

    paymentMethod.addEventListener("change", function () {
        upiDetails.classList.add("hidden");
        cardDetails.classList.add("hidden");

        if (this.value === "UPI") {
            upiDetails.classList.remove("hidden");
        } else if (this.value === "Credit Card" || this.value === "Debit Card") {
            cardDetails.classList.remove("hidden");
        }
    });
</script>

</body>
</html>
