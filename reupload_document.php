<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["booking_id"])) {
    $booking_id = intval($_POST["booking_id"]);

    if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] == 0) {
        $target_dir = "uploads/";
        $file_name = time() . "_" . basename($_FILES["id_proof"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error_message'] = "Only JPG, JPEG, PNG, and PDF files are allowed.";
            header("Location: booking_details.php");
            exit();
        }

        if (move_uploaded_file($_FILES["id_proof"]["tmp_name"], $target_file)) {
            // Update the database with the new document and reset the status to pending
            $update_query = "UPDATE bookings 
                             SET id_proof = ?, 
                                 id_verification = 'Pending', 
                                 is_reupload = 1
                             WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $file_name, $booking_id);
            $stmt->execute();
            $stmt->close();

            // Set success message
            $_SESSION['success_message'] = "Reupload successful! Your document has been submitted for verification.";
        } else {
            $_SESSION['error_message'] = "Failed to upload the file.";
        }
    } else {
        $_SESSION['error_message'] = "No file selected or file upload error.";
    }
    header("Location: booking_details.php");
    exit();
}
?>
