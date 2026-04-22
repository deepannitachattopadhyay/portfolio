<?php
// Simple contact handler with basic validation and sanitization.
// Note: This script requires PHP to run (not supported by static servers like Live Server).

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    header('Allow: POST');
    echo 'Method Not Allowed';
    exit();
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'Contact Form Submission';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Basic server-side validation
$errors = [];
if ($name === '') {
    $errors[] = 'Name is required.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email is required.';
}
if ($message === '') {
    $errors[] = 'Message is required.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo implode(' ', $errors);
    exit();
}

// Sanitize values for email body and headers
$safe_name = filter_var($name, FILTER_SANITIZE_STRING);
$safe_email = filter_var($email, FILTER_SANITIZE_EMAIL);
$safe_subject = filter_var($subject, FILTER_SANITIZE_STRING);
$safe_message = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

// Prevent header injection
$safe_email = str_replace(["\r", "\n"], '', $safe_email);
$safe_name = str_replace(["\r", "\n"], '', $safe_name);

$to = getenv('CONTACT_TO') ?: 'armaansaraswat1@gmail.com'; // default recipient

$body = "From: $safe_name\nE-Mail: $safe_email\nSubject: $safe_subject\nMessage:\n\n" . $safe_message;

// SMTP / PHPMailer configuration (recommended):
// Set environment variables or edit the fallback values below.
$smtp_host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$smtp_port = getenv('SMTP_PORT') ?: 587; // 587 for TLS, 465 for SSL
$smtp_user = getenv('SMTP_USER') ?: 'armaansaraswat1@gmail.com';
$smtp_pass = getenv('SMTP_PASS') ?: ''; // <-- put your App Password here if not using env vars
$smtp_secure = getenv('SMTP_SECURE') ?: 'tls'; // 'tls' or 'ssl'

// If Composer's autoload exists, try sending via PHPMailer SMTP
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_user;
        $mail->Password = $smtp_pass;
        $mail->SMTPSecure = $smtp_secure;
        $mail->Port = (int)$smtp_port;

        // Recipients
        $mail->setFrom($safe_email, $safe_name);
        $mail->addAddress($to);
        $mail->addReplyTo($safe_email, $safe_name);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $safe_subject;
        $mail->Body    = $body;

        $mail->send();
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo 'OK';
            exit();
        } else {
            header('Location: thank-you.html');
            exit();
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log('Mail error: ' . $mail->ErrorInfo);
        echo 'Error sending email. Please try again later.';
        exit();
    }
} else {
    // Fallback to mail() if PHPMailer not installed
    $headers = "From: $safe_name <$safe_email>\r\n" .
               "Reply-To: $safe_email\r\n" .
               "Content-Type: text/plain; charset=utf-8\r\n";

    if (@mail($to, $safe_subject, $body, $headers)) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo 'OK';
            exit();
        } else {
            header('Location: thank-you.html');
            exit();
        }
    } else {
        http_response_code(500);
        echo 'Error sending email. Please try again later.';
        exit();
    }
}

?>
