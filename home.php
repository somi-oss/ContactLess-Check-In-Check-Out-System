<?php
session_start();
include 'db.php'; // Database connection

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch user details
$user_query = "SELECT name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Fetch room details with available room count
$room_query = "
    SELECT 
        r.id, 
        r.room_name, 
        r.room_type, 
        r.price, 
        r.total_rooms,
        COUNT(rn.id) AS available_rooms
    FROM rooms r
    LEFT JOIN room_numbers rn ON r.id = rn.room_id AND rn.is_booked = 0
    GROUP BY r.id
    ORDER BY r.id
";

$room_result = $conn->query($room_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            background: #f8f9fa;
        }
        .sidebar {
            width: 280px;
            height: 100vh;
            background: #343a40;
            position: fixed;
            left: -280px;
            top: 0;
            transition: left 0.3s ease-in-out;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            z-index: 1001;
            overflow-y: auto;
            
        }
        .sidebar a {
            display: flex;
            align-items: center;
            color: #ddd;
            text-decoration: none;
            padding: 12px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .sidebar .user-info {
            text-align: center;
            
            margin-bottom: 20px;
            border-bottom: 1px solid #666;
            padding-bottom: 15px;
            color: #fff;
        }
        .menu-btn {
            position: absolute;
            left: 15px;
            top: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            z-index: 1002;
            color: #343a40;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 0;
            transition: margin-left 0.3s ease-in-out;
            margin-top: 60px;
        }
        .sidebar.active {
            left: 0;
        }
        .main-content.shift {
            margin-left: 280px;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
        }
        .overlay.active {
            display: block;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            font-size: 14px;
        }
        .card:hover {
            transform: scale(1.03);
        }
        .book-btn {
            width: 100%;
            font-weight: bold;
        }
    </style>
</head>
<body>

<button class="menu-btn" id="menu-btn">☰</button>

<div class="sidebar" id="sidebar">
    <div class="user-info">
        <h5><?php echo htmlspecialchars($user['name']); ?></h5>
        <p><?php echo htmlspecialchars($user['email']); ?></p>
    </div>
    <a href="home.php">🏨 Rooms</a>
    <a href="booking_details.php">📅 Booking Details</a>
    <a href="payment_details.php">💳 Payment Details</a>
    <a href="logout.php" class="text-danger">🚪 Logout</a>
</div>

<div class="overlay" id="overlay"></div>

<div class="main-content" id="main-content">
    <h2 class="mb-4">Rooms</h2>

    <div class="row">
        <?php if ($room_result->num_rows > 0): ?>
            <?php while ($room = $room_result->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($room['room_name']); ?></h5>
                            <p class="card-text">Type: <?php echo htmlspecialchars($room['room_type']); ?></p>
                            <p class="card-text">Price: ₹<?php echo number_format($room['price'], 2); ?> / 24 hrs</p>
                            <p class="card-text">Available Rooms: <strong><?php echo max(0, $room['available_rooms']); ?></strong></p>
                            <?php if ($room['available_rooms'] > 0): ?>
                                <a href="book_room.php?room_id=<?php echo urlencode($room['id']); ?>" class="btn btn-primary book-btn">Book Room</a>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>Not Available</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted">No rooms available.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    let menuBtn = document.getElementById("menu-btn");
    let sidebar = document.getElementById("sidebar");
    let overlay = document.getElementById("overlay");
    let content = document.getElementById("main-content");

    menuBtn.addEventListener("click", function () {
        sidebar.classList.toggle("active");
        content.classList.toggle("shift");
        overlay.classList.toggle("active");
    });

    overlay.addEventListener("click", function () {
        sidebar.classList.remove("active");
        content.classList.remove("shift");
        overlay.classList.remove("active");
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
