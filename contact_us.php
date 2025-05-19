<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Required Files
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

if (isset($_POST["send"])) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                
        $mail->Host = 'smtp.gmail.com';                  
        $mail->SMTPAuth = true;                         
        $mail->Username = 'ammuvijayarangan23@gmail.com'; // Your email
        $mail->Password = 'grcokvegwvzmeoex';           // App password (not Gmail password)
        $mail->SMTPSecure = 'ssl';                     
        $mail->Port = 465;                             

        // Recipients
        $mail->setFrom($_POST["email"], $_POST["name"]); 
        $mail->addAddress('ammuvijayarangan23@gmail.com'); // Your receiving email
        $mail->addReplyTo($_POST["email"], $_POST["name"]);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $_POST["subject"]; 
        $mail->Body = "
            <h4><strong>Name:</strong> {$_POST['name']}</h4>
            <h4><strong>Email:</strong> {$_POST['email']}</h4>
            <p><strong>Message:</strong><br>{$_POST['message']}</p>
        ";

        // Send Mail
        $mail->send();  // ✅ This was missing!

        echo "<script>
                alert('Message sent successfully!');
                document.location.href = '../index.html'; 
              </script>";

    } catch (Exception $e) {
        echo "<script>
                alert('Error: {$mail->ErrorInfo}');
                document.location.href = '../index.html';
              </script>";
    }
}
?>
