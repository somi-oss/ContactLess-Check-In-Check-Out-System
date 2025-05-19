<?php
session_start();
include 'db.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit(); 
}

$user_id = $_SESSION["user_id"];

// Fetch all booking details for the logged-in user
$booking_query = "SELECT b.id, b.checkin_date, b.checkout_date, b.price, b.id_verification, b.id_proof,
                         r.room_name,r.room_type, u.name, u.mobile
                  FROM bookings b
                  JOIN rooms r ON b.room_id = r.id
                  JOIN users u ON b.user_id = u.id
                  WHERE b.user_id = ? 
                  ORDER BY b.id DESC"; // Fetch all bookings

$stmt = $conn->prepare($booking_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 800px;
        }
        .booking-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .booking-card h5 {
            margin-bottom: 15px;
        }
        .id-status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }
        .verified {
            background-color: #d4edda;
            color: #155724;
        }
        .pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h3 class="mb-4 text-center">Your Booking Details</h3>
    <a href="home.php" class="btn btn-secondary mt-4">Back to Dashboard</a>
    <br><br>
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

    <?php if (empty($bookings)): ?>
        <p class="text-center">No bookings found.</p>
    <?php else: ?>
        <?php foreach ($bookings as $booking): 
            $checkin_date = strtotime($booking['checkin_date']);
            $checkout_date = strtotime($booking['checkout_date']);
            $total_days = ($checkout_date - $checkin_date) / (60 * 60 * 24);
            $total_amount = $total_days * $booking['price'];

            // Determine ID Verification status
            if ($booking['id_verification'] === 'Verified') {
                $id_status = 'Verified';
                $id_class = 'verified';
            } elseif ($booking['id_verification'] === 'Rejected') {
                $id_status = 'Rejected';
                $id_class = 'rejected';
            } else {
                $id_status = 'Pending';
                $id_class = 'pending';
            }
        ?>
        <div class="booking-card">
            <h5>Booking ID: #<?php echo $booking['id']; ?></h5>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['name']); ?></p>
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($booking['mobile']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_name']); ?></p>
            <p><strong>Check-in Date:</strong> <?php echo date("d M Y", $checkin_date); ?></p>
            <p><strong>Check-out Date:</strong> <?php echo date("d M Y", $checkout_date); ?></p>
            <p><strong>Total Days:</strong> <?php echo $total_days; ?> days</p>
            <p><strong>Total Amount:</strong> ₹<?php echo number_format($total_amount, 2); ?></p>
            <p><strong>ID Verification Status:</strong> <span class="id-status <?php echo $id_class; ?>"><?php echo $id_status; ?></span></p>

            <!-- Reupload Document -->
            <?php if ($booking['id_verification'] === 'Rejected'): ?>
                <form action="reupload_document.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <div class="mb-3">
                        <label for="id_proof" class="form-label">Reupload ID Proof</label>
                        <input type="file" name="id_proof" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm">Reupload</button>
                </form>
            <?php endif; ?>

            <!-- Payment Button -->
            <button class="btn btn-primary mt-2" onclick="initiatePayment(<?php echo $booking['id']; ?>, '<?php echo $booking['id_verification']; ?>')">Proceed to Payment</button>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script>
    function initiatePayment(bookingId, verificationStatus) {
        if (verificationStatus === "Verified") { 
            window.location.href = "payment.php?booking_id=" + bookingId;
        } else {
            alert("Payment is not allowed. Your ID verification is still Pending or Rejected.");
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
