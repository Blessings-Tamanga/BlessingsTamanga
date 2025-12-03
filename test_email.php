<?php
include_once 'helpers/EmailService.php';

$emailService = new EmailService([
    'from_email' => 'blessings.tamanga@example.com',
    'from_name' => 'Blessings E. Tamanga'
]);

// Test email
$to_email = 'your-test-email@gmail.com'; // Replace with your email
$to_name = 'Test User';
$subject = 'Test Reply from Portfolio';
$message = 'This is a test message to verify email functionality is working.';

if ($emailService->sendCustomReply($to_email, $to_name, $subject, $message)) {
    echo "✅ Test email sent successfully to {$to_email}!";
} else {
    echo "❌ Failed to send test email. Check your server email configuration.";
}
?>