<?php
session_start();
include 'db.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch user details
$user_query = "SELECT name, email, mobile FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows == 0) {
    die("User not found.");
}

$user = $user_result->fetch_assoc();
$stmt->close();

// Check if room_id is provided
if (!isset($_GET["room_id"]) || empty($_GET["room_id"])) {
    die("Invalid room selection.");
}

$room_id = intval($_GET["room_id"]);

// Fetch room details
$room_query = "SELECT r.room_name, r.price, r.total_rooms, rn.room_number 
               FROM rooms r
               INNER JOIN room_numbers rn ON r.id = rn.room_id
               WHERE r.id = ? AND r.status = 'Available'";

$stmt = $conn->prepare($room_query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room_result = $stmt->get_result();

if ($room_result->num_rows == 0) {
    die("Room not available.");
}

$room = $room_result->fetch_assoc();
$stmt->close();

// Ensure rooms are available
if ($room["total_rooms"] <= 0) {
    die("No available rooms.");
}

// Handle booking form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $checkin_date = $_POST["checkin_date"];
    $checkout_date = $_POST["checkout_date"];

    // Validate dates
    if (strtotime($checkin_date) < strtotime(date("Y-m-d")) || strtotime($checkout_date) <= strtotime($checkin_date)) {
        echo "<script>alert('Invalid check-in or check-out date.'); window.location.href='book_room.php?room_id=$room_id';</script>";
        exit();
    }

    // Handle ID Proof Upload
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file = $_FILES["id_proof"];
    $allowed_types = ["image/jpeg", "image/png", "application/pdf"];
    $max_size = 2 * 1024 * 1024; // 2MB

    if ($file["size"] > $max_size || !in_array($file["type"], $allowed_types)) {
        echo "<script>alert('Invalid file. Only JPEG, PNG, and PDF under 2MB allowed.'); window.location.href='book_room.php?room_id=$room_id';</script>";
        exit();
    }

    $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $file_name = "id_proof_" . time() . "_" . $user_id . "." . $file_extension;
    $file_path = $upload_dir . $file_name;

    if (!move_uploaded_file($file["tmp_name"], $file_path)) {
        echo "<script>alert('File upload failed. Try again.'); window.location.href='book_room.php?room_id=$room_id';</script>";
        exit();
    }

    // Fetch an available room number (where is_booked = 0)
    $available_room_query = "SELECT id, room_number FROM room_numbers WHERE room_id = ? AND is_booked = 0 LIMIT 1";
    $stmt = $conn->prepare($available_room_query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $available_room_result = $stmt->get_result();

    if ($available_room_result->num_rows == 0) {
        die("No available room numbers.");
    }

    $available_room = $available_room_result->fetch_assoc();
    $room_number = $available_room["room_number"];
    $room_number_id = $available_room["id"];

    // Mark the room as booked
    $update_room_status_query = "UPDATE room_numbers SET is_booked = 1 WHERE id = ?";
    $stmt = $conn->prepare($update_room_status_query);
    $stmt->bind_param("i", $room_number_id);
    $stmt->execute();
    $stmt->close();
    
    // Fetch the user's name based on user_id
    $name_query = "SELECT name FROM users WHERE id = ?";
    $stmt_name = $conn->prepare($name_query);
    $stmt_name->bind_param("i", $user_id);
    $stmt_name->execute();
    $stmt_name->bind_result($user_name);
    $stmt_name->fetch();
    $stmt_name->close();

    // Insert booking record with the available room number and user's name
    $insert_query = "INSERT INTO bookings (user_id, name, room_id, room_type, price, checkin_date, checkout_date, booking_date, status, id_proof, room_number) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'Confirmed', ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("isissssss", $user_id, $user_name, $room_id, $room["room_name"], $room["price"], $checkin_date, $checkout_date, $file_path, $room_number);
    $booking_success = $stmt->execute();
    $last_booking_id = $stmt->insert_id;
    $stmt->close();


    if ($booking_success) {
        // Reduce the available room count
        $update_room_query = "UPDATE rooms SET total_rooms = total_rooms - 1 WHERE id = ?";
        $stmt = $conn->prepare($update_room_query);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $stmt->close();

        echo "<h2 style='text-align:center; margin-top:20px;'>Booking Successful!</h2>";
        echo "<script>setTimeout(function(){ window.location.href='booking_details.php?booking_id=$last_booking_id'; }, 2000);</script>";
        exit();
    } else {
        echo "<script>alert('Booking failed. Try again later.'); window.location.href='rooms.php';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            width: 100%;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h3 class="mb-3">Book Room: <?php echo htmlspecialchars($room["room_name"]); ?></h3>
    <p><strong>Price:</strong> ₹<?php echo number_format($room["price"], 2); ?> / night</p>
    <p><strong>Available Rooms:</strong> <?php echo $room["total_rooms"]; ?></p>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Customer Name</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Customer Email</label>
            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Customer Mobile</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['mobile']); ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Check-in Date</label>
            <input type="date" class="form-control" name="checkin_date" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Check-out Date</label>
            <input type="date" class="form-control" name="checkout_date" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Upload ID Proof (PDF, JPG, PNG)</label>
            <input type="file" class="form-control" name="id_proof" accept=".jpg, .jpeg, .png, .pdf" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit for Verification</button>
    </form>
</div>

</body>
</html>