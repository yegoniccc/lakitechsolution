<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php'; // Contains $smtpHost, etc.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = ['status' => 'error', 'message' => 'Unexpected error.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $message) {
        $mail = new PHPMailer(true);

        try {
            // SMTP setup
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUsername;
            $mail->Password = $smtpPassword;
            $mail->SMTPSecure = $smtpSecure;
            $mail->Port = $smtpPort;

            // Send to admin
            $mail->setFrom($smtpUsername, 'Laki Tech Solutions');
            $mail->addAddress($siteEmail, "Admin");
            $mail->addReplyTo($email, $name);

            $mail->isHTML(true);
            $mail->Subject = "New Contact Message from {$name}";
            $mail->Body = "
                <h3>New Inquiry from Website</h3>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Phone:</strong> {$phone}</p>
                <p><strong>Message:</strong><br>{$message}</p>
            ";

            $mail->send();

            // Auto-reply to user
            $reply = new PHPMailer(true);
            $reply->isSMTP();
            $reply->Host = $smtpHost;
            $reply->SMTPAuth = true;
            $reply->Username = $smtpUsername;
            $reply->Password = $smtpPassword;
            $reply->SMTPSecure = $smtpSecure;
            $reply->Port = $smtpPort;

            $reply->setFrom($smtpUsername, 'Laki Tech Solutions');
            $reply->addAddress($email, $name);
            $reply->isHTML(true);
            $reply->Subject = "Thank You for Contacting Laki Tech Solutions";
            $reply->Body = "
                <h2>Hi {$name},</h2>
                <p>Thank you for reaching out to us! We’ve received your message and will get back to you soon.</p>
                <p><em>Your message:</em><br>{$message}</p>
                <p>Kind regards,<br><strong>Laki Tech Solutions Team</strong></p>
            ";

            $reply->send();

            $response = ['status' => 'success', 'message' => 'Message sent successfully!'];
        } catch (Exception $e) {
            $response = ['status' => 'error', 'message' => 'Mailer Error: ' . $mail->ErrorInfo];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Please fill in all required fields correctly.'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
