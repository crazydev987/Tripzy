<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Stripe
\Stripe\Stripe::setApiKey("sk_test_your_secret_key_here");

// Input
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input['payment_method_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

try {
    // Stripe PaymentIntent
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $input['price'] * 100,
        'currency' => strtolower($input['currency']),
        'payment_method' => $input['payment_method_id'],
        'confirmation_method' => 'manual',
        'confirm' => true,
        'receipt_email' => $input['email'],
        'description' => "Booking: " . $input['hotelName'] . " (" . $input['checkIn'] . " - " . $input['checkOut'] . ")",
    ]);

    if ($paymentIntent->status === 'succeeded') {
        // Setup PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.email.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'email@gmail.com'; // your Gmail
            $mail->Password   = 'app_pass_word';   // App Password (not Gmail password!)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('email@gmail.com', 'Tripzy');
            $mail->addAddress($input['email'], $input['name']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Booking Confirmation - " . $input['hotelName'];
            $mail->Body    = "
                <h2>Booking Confirmed</h2>
                <p>Dear {$input['name']},</p>
                <p>Your booking at <strong>{$input['hotelName']}</strong> has been confirmed.</p>
                <p><b>Check-In:</b> {$input['checkIn']}<br>
                <b>Check-Out:</b> {$input['checkOut']}<br>
                <b>Amount Paid:</b> {$input['currency']} {$input['price']}</p>
                <p>Thank you for booking with Tripzy!</p>
            ";

            $mail->send();

            echo json_encode(['success' => true, 'message' => 'Payment successful, booking confirmed, email sent']);
        } catch (Exception $e) {
            echo json_encode(['success' => true, 'message' => 'Payment successful, but email not sent: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment not completed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
