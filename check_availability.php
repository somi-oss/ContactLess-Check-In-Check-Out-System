<?php
$servername = "localhost";
$username = "root";  
$password = "";      
$database = "hotel_management";

// Connect to database
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form inputs safely
$checkin = $conn->real_escape_string($_POST['checkin']);
$checkout = $conn->real_escape_string($_POST['checkout']);
$room_type = $conn->real_escape_string($_POST['room_type']);

// Query to check available rooms
$sql = "SELECT r.total_rooms - COALESCE(SUM(b.booked_rooms), 0) AS available_rooms 
        FROM rooms r
        LEFT JOIN (
            SELECT room_type, COUNT(*) AS booked_rooms 
            FROM bookings 
            WHERE (checkin_date < '$checkout' AND checkout_date > '$checkin')
            GROUP BY room_type
        ) b ON r.room_type = b.room_type
        WHERE r.room_type = '$room_type'
        GROUP BY r.total_rooms";

$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row && $row['available_rooms'] > 0) {
    echo "Rooms Available: " . $row['available_rooms'];
} else {
    echo "No Rooms Available";
}

$conn->close();
?>
