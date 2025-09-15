<?php
require 'vendor/autoload.php'; // Stripe & PHPMailer
require 'env_loader.php';      // your .env loader if needed

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load .env variables (like STRIPE_SECRET, GMAIL_USER, GMAIL_PASS)
loadEnv(__DIR__ . '/.env');

// Get posted JSON
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input['name'], $input['price'], $input['currency'], $input['passenger'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$hotelName = $input['name'];
$price = $input['price'];
$currency = strtolower($input['currency']);
$passenger = $input['passenger'];
$passengerName = $passenger['name'];
$passengerEmail = $passenger['email'];

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET'));

try {
    // 1. Create Stripe Checkout session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => $currency,
                'product_data' => ['name' => $hotelName],
                'unit_amount' => $price * 100, // convert to cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost:3000/book-hotel?success=true',
        'cancel_url' => 'http://localhost:3000/book-hotel?cancel=true',
    ]);

    // 2. Send booking confirmation email via PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('GMAIL_USER'); // your Gmail
    $mail->Password = getenv('GMAIL_PASS'); // Gmail App Password
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(getenv('GMAIL_USER'), 'Tripzy Booking');
    $mail->addAddress($passengerEmail, $passengerName);

    $mail->isHTML(true);
    $mail->Subject = "Booking Confirmation for $hotelName";
    $mail->Body = "
        <h2>Booking Confirmed!</h2>
        <p>Dear $passengerName,</p>
        <p>Your booking for <strong>$hotelName</strong> is confirmed.</p>
        <p>Amount Paid: $price $currency</p>
        <p>Thank you for booking with Tripzy!</p>
    ";

    $mail->send();

    // 3. Return Stripe session ID
    echo json_encode(['id' => $session->id]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
