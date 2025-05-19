<?php
require 'db.php'; // Your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if email exists
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Redirect to reset password page with email in URL
        header("Location: reset_password.php?email=" . urlencode($email));
        exit;
    } else {
        echo "<script>alert('Email not found!'); window.history.back();</script>";
        exit;
    }
}
?>
