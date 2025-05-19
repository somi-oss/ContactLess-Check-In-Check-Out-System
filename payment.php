<?php
session_start();
include 'db.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get Booking ID from URL
if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    die("Invalid booking ID.");
}

$booking_id = intval($_GET['booking_id']);
$user_id = $_SESSION["user_id"];

// Fetch Booking Details
$query = "SELECT b.id, b.checkin_date, b.checkout_date, b.price, b.id_verification, b.payment_status, 
                 r.room_name, u.name, u.mobile
          FROM bookings b
          JOIN rooms r ON b.room_id = r.id
          JOIN users u ON b.user_id = u.id
          WHERE b.id = ? AND b.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No booking found or unauthorized access.");
}

$booking = $result->fetch_assoc();
$stmt->close();

// Ensure the booking is verified before payment
if ($booking['id_verification'] !== 'Verified') {
    die("Payment is not allowed. Your ID verification is still Pending or Rejected.");
}

// Calculate Total Amount
$checkin_date = strtotime($booking['checkin_date']);
$checkout_date = strtotime($booking['checkout_date']);
$total_days = ($checkout_date - $checkin_date) / (60 * 60 * 24);
$total_amount = $total_days * $booking['price'];

// Handle Payment Submission
// Handle Payment Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_payment_btn'])) { 
    $transaction_id = trim($_POST['transaction_id']);

    if (empty($transaction_id)) {
        $_SESSION['error_message'] = "Transaction ID is required.";
    } else {
        $update_query = "UPDATE bookings SET payment_status = 'Pending Verification', transaction_id = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $transaction_id, $booking_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Payment marked as Pending. Admin will verify your transaction.";
            header("Location: payment_details.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error processing request.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .table {
            width: 100%;
            margin-bottom: 20px;
        }
        .table th, .table td {
            text-align: left;
            padding: 8px;
        }
        .btn {
            margin-top: 20px;
            width: 100%;
        }
        #qr-container {
            display: none;
            margin-top: 20px;
        }
        #qr-image {
            width: 250px;
            height: auto;
        }
    </style>
    <script>
        function showQR() {
            document.getElementById('qr-container').style.display = 'block';
        }
    </script>
</head>
<body>

<div class="container">
    <h3 class="text-center mb-4">Payment for Booking ID: #<?php echo $booking['id']; ?></h3>

    <table class="table table-bordered">
        <tr>
            <th>Name</th>
            <td><?php echo htmlspecialchars($booking['name']); ?></td>
        </tr>
        <tr>
            <th>Phone Number</th>
            <td><?php echo htmlspecialchars($booking['mobile']); ?></td>
        </tr>
        <tr>
            <th>Room Type</th>
            <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
        </tr>
        <tr>
            <th>Check-in Date</th>
            <td><?php echo date("d M Y", $checkin_date); ?></td>
        </tr>
        <tr>
            <th>Check-out Date</th>
            <td><?php echo date("d M Y", $checkout_date); ?></td>
        </tr>
        <tr>
            <th>Total Days</th>
            <td><?php echo $total_days; ?> days</td>
        </tr>
        <tr>
            <th>Amount per Day</th>
            <td>₹<?php echo number_format($booking['price'], 2); ?></td>
        </tr>
        <tr>
            <th>Total Amount</th>
            <td>₹<?php echo number_format($total_amount, 2); ?></td>
        </tr>
    </table>

    <!-- Show QR Code on Button Click -->
    <button type="button" class="btn btn-success" onclick="showQR()">Proceed to Pay ₹<?php echo number_format($total_amount, 2); ?></button>

    <!-- QR Code Section -->
    <div id="qr-container">
        <h5 class="mt-3">Scan the QR Code to Pay</h5>
        <img id="qr-image" src="your_qr_code_image.jpg" alt="UPI QR Code">
        
        <form method="POST">
    <input type="hidden" name="confirm_payment" value="1">
    
    <!-- Transaction ID Input -->
    <div class="mb-3 mt-3">
        <label for="transaction_id" class="form-label">Enter Transaction ID:</label>
        <input type="text" id="transaction_id" name="transaction_id" class="form-control" required placeholder="Enter Transaction ID">
    </div>

    <!-- Add name attribute to submit button -->
    <button type="submit" name="confirm_payment_btn" class="btn btn-primary mt-3">I Have Paid</button>
</form>

    </div>

    <a href="booking_details.php" class="btn btn-secondary mt-2">Back to Bookings</a>
</div>

</body>
</html>
