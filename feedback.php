<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = $_GET['booking_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maintenance = $_POST['maintenance'];
    $checkin = $_POST['checkin'];
    $digital_key = $_POST['digital_key'];
    $time_saving = $_POST['time_saving'];
    $satisfaction = $_POST['satisfaction'];
    $comments = mysqli_real_escape_string($conn, $_POST['comments']);

    $insert = "INSERT INTO feedback (
        user_id, booking_id, maintenance, checkin, digital_key, time_saving, satisfaction, comments
    ) VALUES (
        '$user_id', '$booking_id', '$maintenance', '$checkin', '$digital_key', '$time_saving', '$satisfaction', '$comments'
    )";

    if (mysqli_query($conn, $insert)) {
        echo "<script>alert('Thank you for your valuable feedback!'); window.location.href='home.php';</script>";
    } else {
        echo "<script>alert('Something went wrong. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hotel Feedback</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
            text-align: center;
            padding-top: 50px;
        }
        .container {
            background: white;
            display: inline-block;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 450px;
        }
        h2 {
            color: #333;
        }
        label {
            text-align: left;
            display: block;
            font-weight: bold;
            margin-top: 15px;
        }
        select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        textarea {
            resize: vertical;
        }
        button {
            margin-top: 25px;
            padding: 12px 25px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #388e3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📝 Your Feedback Matters!</h2>
        <form method="POST">
            <label>1. Room Maintenance</label>
            <select name="maintenance" required>
                <option value="">-- Select --</option>
                <option value="Excellent">Excellent</option>
                <option value="Good">Good</option>
                <option value="Average">Average</option>
                <option value="Poor">Poor</option>
            </select>

            <label>2. Contactless Check-In Experience</label>
            <select name="checkin" required>
                <option value="">-- Select --</option>
                <option value="Very Easy">Very Easy</option>
                <option value="Easy">Easy</option>
                <option value="Neutral">Neutral</option>
                <option value="Difficult">Difficult</option>
            </select>

            <label>3. Was the Digital Key (QR Code) Helpful?</label>
            <select name="digital_key" required>
                <option value="">-- Select --</option>
                <option value="Very Helpful">Very Helpful</option>
                <option value="Helpful">Helpful</option>
                <option value="Not Sure">Not Sure</option>
                <option value="Not Helpful">Not Helpful</option>
            </select>

            <label>4. Did contactless check-in save your time?</label>
            <select name="time_saving" required>
                <option value="">-- Select --</option>
                <option value="Yes">Yes</option>
                <option value="Somewhat">Somewhat</option>
                <option value="No">No</option>
            </select>

            <label>5. Overall Satisfaction</label>
            <select name="satisfaction" required>
                <option value="">-- Select --</option>
                <option value="⭐⭐⭐⭐⭐">⭐⭐⭐⭐⭐ Excellent</option>
                <option value="⭐⭐⭐⭐">⭐⭐⭐⭐ Very Good</option>
                <option value="⭐⭐⭐">⭐⭐⭐ Good</option>
                <option value="⭐⭐">⭐⭐ Fair</option>
                <option value="⭐">⭐ Poor</option>
            </select>

            <label>6. Additional Comments</label>
            <textarea name="comments" rows="4" placeholder="Share your thoughts..."></textarea>

            <button type="submit">Submit Feedback</button>
        </form>
    </div>
</body>
</html>
