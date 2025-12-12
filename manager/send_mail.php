<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';

function sendEmail( $email,  $new_status, $subject = null, $body = null) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'anujkumar2727272@gmail.com';
        $mail->Password = 'xlnz ukqd sjgh okwg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('anujkumar2727272@gmail.com', 'Leave Department');
        $mail->addAddress( $email);

        // Content
        $mail->isHTML(true);
        if ($subject === null) {
            $mail->Subject = "Leave Request $new_status";
        } else {
            $mail->Subject = $subject;
        }

        if ($body === null) {
            $mail->Body = "<p>Hi,</p><p>Your leave request has been <strong> $new_status</strong> by the manager.</p><p>Regards,<br>Leave Management System</p>";
        } else {
            $mail->Body = $body;
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
