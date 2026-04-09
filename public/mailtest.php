<?php
/**
 * PHPMailer direct include test
 * Path: /var/www/html/public/mailtest.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Load PHPMailer manually from your ThirdParty folder
require '/var/www/html/app/ThirdParty/PHPMailer/PHPMailer.php';
require '/var/www/html/app/ThirdParty/PHPMailer/SMTP.php';
require '/var/www/html/app/ThirdParty/PHPMailer/Exception.php';

echo "<pre>Initializing PHPMailer test...\n";

$mail = new PHPMailer(true);

try {
    echo "Configuring Gmail SMTP...\n";

    // === SMTP Configuration ===
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'iotxad@gmail.com';        // Gmail address
    $mail->Password   = 'xsazziokcgvagbft';        // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // === Enable Debugging ===
    $mail->SMTPDebug  = 3; // detailed logs
    $mail->Debugoutput = function($str, $level) {
        echo "DEBUG [$level]: " . htmlspecialchars($str) . "\n";
    };

    // === Sender & Recipient ===
    $mail->setFrom('iotxad@gmail.com', 'PHPMailer Test');
    $mail->addAddress('iotxad@gmail.com', 'Self Test');
    $mail->addReplyTo('iotxad@gmail.com', 'Reply Test');

    // === Email Content ===
    $mail->isHTML(true);
    $mail->Subject = 'Manual PHPMailer test (from /var/www/html/public)';
    $mail->Body    = '<h3>✅ Test Email sent from /var/www/html/app/ThirdParty/PHPMailer</h3>';
    $mail->AltBody = 'Plain text version of the message.';

    echo "Attempting to send message...\n";
    $mail->send();
    echo "\n✅ Message sent successfully!\n";

} catch (Exception $e) {
    echo "\n❌ PHPMailer Exception: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "Error Info: " . htmlspecialchars($mail->ErrorInfo) . "\n";
} catch (Throwable $t) {
    echo "\n❌ PHP Error: " . htmlspecialchars($t->getMessage()) . "\n";
}

echo "\nScript completed.\n</pre>";
?>

