<?php
require 'db.php'; // Your database connection

if (isset($_GET['email'])) {
    $email = $_GET['email'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $query = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $new_password, $email);
        $stmt->execute();

        echo "<script>alert('Password reset successful!'); window.location.href='login.php';</script>";
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Reset Password</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container py-5">
        <h2 class="mb-4">Reset Password</h2>
        <form method="post">
            <div class="mb-3">
                <label>New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Reset Password</button>
        </form>
    </body>
    </html>
    <?php
} else {
    echo "Invalid access.";
}
?>
