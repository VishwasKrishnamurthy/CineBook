<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    die("❌ Error: User not logged in.");
}

// Fetch and validate required fields
$email = $_SESSION['email'];
$movie_id = $_POST['movie_id'] ?? null;
$theater_id = $_POST['theater_id'] ?? null;
$show_id = $_POST['show_id'] ?? null;
$show_date = $_POST['show_date'] ?? null;
$show_time = $_POST['show_time'] ?? null;
$selected_seats = $_POST['seats'] ?? [];
$total_price = $_POST['total_price'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;
 $language = $_POST['language'] ?? "Unknown"; 

// Validate required parameters
if (!$email || !$movie_id || !$theater_id || !$show_id || !$show_date || !$show_time || empty($selected_seats) || !$total_price || !$payment_method) {
    die("❌ Error: Missing required parameters.");
}

// Ensure $selected_seats is an array
if (!is_array($selected_seats)) {
    $selected_seats = explode(",", $selected_seats);
}

// Convert selected seats array to string format for database
$seat_numbers = implode(",", $selected_seats);
$status = "Confirmed";

// 1️⃣ **Check if selected seats are already booked**
$seat_check = $conn->prepare("
    SELECT seat_numbers FROM bookings 
    WHERE show_id = ? AND show_date = ? AND show_time = ?
");
$seat_check->bind_param("iss", $show_id, $show_date, $show_time);
$seat_check->execute();
$result = $seat_check->get_result();
$seat_check->close();

// Convert booked seats into an array
$existingSeats = [];
while ($row = $result->fetch_assoc()) {
    $existingSeats = array_merge($existingSeats, explode(',', $row['seat_numbers']));
}

// Ensure $existingSeats is an array before using array_intersect
if (!is_array($existingSeats)) {
    $existingSeats = [];
}

// Find duplicate seats
$duplicateSeats = array_intersect($selected_seats, $existingSeats);

if (!empty($duplicateSeats)) {
    die("❌ Error: The following seats are already booked: " . implode(', ', $duplicateSeats));
}

// 2️⃣ **Generate a unique transaction ID**
$transaction_id = uniqid('TXN_');

// 3️⃣ **Process Payment (Only store booking if payment is successful)**
$insert_payment = $conn->prepare("
    INSERT INTO payments (email, show_id, seat_numbers, amount, payment_method, payment_status, transaction_id, created_at) 
    VALUES (?, ?, ?, ?, ?, 'Success', ?, NOW())
");

if (!$insert_payment) {
    die("❌ Error in payment query preparation: " . $conn->error);
}

$insert_payment->bind_param("sisdss", $email, $show_id, $seat_numbers, $total_price, $payment_method, $transaction_id);

if ($insert_payment->execute()) {
    $payment_id = $insert_payment->insert_id;
    $insert_payment->close();

    // 4️⃣ **Insert booking details only after payment success**
    $stmt = $conn->prepare("
        INSERT INTO bookings (email, movie_id, theater_id, show_id, show_date, show_time, seat_numbers, total_price, status, booking_time, payment_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
    ");

    if (!$stmt) {
        die("❌ Error in booking query preparation: " . $conn->error);
    }

    $stmt->bind_param("siiisssdsi", $email, $movie_id, $theater_id, $show_id, $show_date, $show_time, $seat_numbers, $total_price, $status, $payment_id);

    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id;
        $stmt->close();

        // 5️⃣ **Redirect to success page with booking ID**
        header("Location: success.php?booking_id=" . $booking_id);
        exit();
    } else {
        die("❌ Error: Booking failed.");
    }
} else {
    die("❌ Error: Payment failed.");
}

// Close database connection
$conn->close();
?>
