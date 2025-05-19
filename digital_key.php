<?php
session_start();
include('db.php');               // DB connection
include('phpqrcode/qrlib.php'); // QR code library

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Required Files
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

$user_id = $_SESSION['user_id'];
$booking_id = $_GET['booking_id'];     // Pass it as URL parameter like ?booking_id=3

// Fetch room number and existing digital key from the database
$check = mysqli_query($conn, "SELECT room_type, room_number, digital_key FROM bookings WHERE id = $booking_id AND user_id = $user_id");
$row = mysqli_fetch_assoc($check);

// If booking not found or doesn't belong to user
if (!$row) {
    die("Invalid booking or access denied.");
}

$room_type = $row['room_type'];  // Fetch the room type
$room_number = $row['room_number'];  // Fetch the room number assigned to the booking
$existing_key = $row['digital_key'];

if (!empty($existing_key)) {
    // If digital key already exists, use it
    $digital_key = $existing_key;
} else {
    // Generate a new digital key using the room number
    $digital_key = $room_number . "-" . $user_id . "-" . uniqid();

    // Save new digital key to DB
    $filename = $digital_key . ".png";
    $update = "UPDATE bookings SET digital_key = '$filename' WHERE id = $booking_id";

    mysqli_query($conn, $update);
}

// Path to QR image
$qr_file = "../qrcodes/" . $digital_key . ".png";

// ✅ Fetch user's email and name from DB first
$user_query = mysqli_query($conn, "SELECT email, name FROM users WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($user_query);
$user_email = $user_data['email'];
$user_name = $user_data['name'];

// Generate QR code only if not already saved
if (!file_exists($qr_file)) {
    QRcode::png($digital_key, $qr_file);

    // ✅ Initialize PHPMailer object
    $mail = new PHPMailer(true); // ADD THIS LINE

    // ✅ Send email with the QR code attachment
    try {
        // Your PHPMailer configuration here
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ammuvijayarangan23@gmail.com';
        $mail->Password   = 'grcokvegwvzmeoex';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('ammuvijayarangan23@gmail.com', 'HITS HOTEL');
        $mail->addAddress($user_email, $user_name);

        // Attach QR code
        $mail->addAttachment($qr_file, 'DigitalKey.png');

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your Hotel Digital Key';
        $mail->Body    = "
            <h3>Hello $user_name,</h3>
            <p>Thank you for your booking! Attached is your digital key (QR Code) for contactless check-in.</p>
            <p><strong>Booking ID:</strong> $booking_id</p>
            <p><strong>Room Number:</strong> $room_number</p>
            <p>Scan this QR in your room and access your room.</p>
            <br>
            <p>Regards,<br>Hotel Team</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Digital Key</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            text-align: center;
            padding-top: 50px;
            margin: 0;
            background-color: white;
        }

        .container {
            background: white;
            display: inline-block;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        h2 {
            color: #2e7d32;
            font-size: 28px;
        }

        img {
            margin-top: 20px;
            width: 200px;
        }

        a.download, a.dashboard {
            display: inline-block;
            margin-top: 25px;
            margin-right: 10px;
            padding: 12px 20px;
            text-decoration: none;
            font-size: 16px;
            border-radius: 10px;
            color: white;
            background-color: #388e3c;
            transition: background-color 0.3s ease;
        }

        a.download:hover, a.dashboard:hover {
            background-color: #2e7d32;
        }

        a.dashboard {
            background-color: #0288d1;
        }

        a.dashboard:hover {
            background-color: #0277bd;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>🔐 Your Digital Key</h2>
        
        <!-- Display Booking ID, User Name, Room Type, and Room Number -->
        <p><strong>Booking ID:</strong> <?php echo $booking_id; ?></p>
        <p><strong>User Name:</strong> <?php echo $user_name; ?></p>
        <p><strong>Room Type:</strong> <?php echo $room_type; ?></p>
        <p><strong>Room Number:</strong> <?php echo $room_number; ?></p>
        
        <img src="<?php echo $qr_file; ?>" alt="Digital Key"><br><br>
        <p>This is your QR code. Use it at the hotel for contactless check-in.</p>

        <a href="<?php echo $qr_file; ?>" class="download" download> Download QR Code</a>
        <a href="feedback.php?booking_id=<?php echo $booking_id; ?>" class="dashboard" style="background-color:#f57c00;">Give Feedback</a>
        <a href="home.php" class="dashboard">Back to Dashboard</a>
         </div>
</body>
</html>
