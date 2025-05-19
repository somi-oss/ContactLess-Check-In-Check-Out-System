<?php
session_start();
include 'db.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch payment details for the logged-in user
$query = "SELECT b.id, b.checkin_date, b.checkout_date, b.price, b.payment_status, b.transaction_id, r.room_name
          FROM bookings b
          JOIN rooms r ON b.room_id = r.id
          WHERE b.user_id = ?
          ORDER BY b.id DESC"; // Sorting in descending order


$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h3 class="text-center mb-4">Your Payment Details</h3>
    <a href="home.php" class="btn btn-primary">Back to Dashboard</a>
    <br><br>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <table class="table table-bordered">
    <thead>
    <tr>
        <th>Booking ID</th>
        <th>Room Type</th>
        <th>Check-in</th>
        <th>Check-out</th>
        <th>Amount</th>
        <th>Transaction ID</th>
        <th>Payment Status</th>
        <th>Digital Key</th> <!-- New column -->
    </tr>
</thead>

        <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <?php
            $checkin = new DateTime($row['checkin_date']);
            $checkout = new DateTime($row['checkout_date']);
            $days = $checkin->diff($checkout)->days;
            if ($days == 0) $days = 1;
            $total = $days * $row['price'];
        ?>
        <tr>
            <td>#<?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['room_name']); ?></td>
            <td><?php echo date("d M Y", strtotime($row['checkin_date'])); ?></td>
            <td><?php echo date("d M Y", strtotime($row['checkout_date'])); ?></td>
            <td>₹<?php echo number_format($total, 2); ?></td>
            <td><?php echo htmlspecialchars($row['transaction_id'] ?: 'N/A'); ?></td>
            <td>
                <?php if ($row['payment_status'] === 'Verified'): ?>
                    <span class="badge bg-success">Verified</span>
                <?php elseif ($row['payment_status'] === 'Pending Verification'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                <?php else: ?>
                    <span class="badge bg-danger">Failed</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($row['payment_status'] === 'Verified'): ?>
                    <a href="digital_key.php?booking_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">View Key</a>
                <?php else: ?>
                    <span class="text-muted">Not Available</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

    </table>
    
</div>

</body>
</html> 