<?php
// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get query parameters
$city = isset($_GET['city']) ? strtoupper(trim($_GET['city'])) : null;
$checkIn = $_GET['checkIn'] ?? null;
$checkOut = $_GET['checkOut'] ?? null;

if (!$city || !$checkIn || !$checkOut) {
    echo json_encode(['success' => false, 'hotels' => [], 'message' => 'Missing required parameters']);
    exit;
}

// Dummy hotel data for sandbox/demo
$dummyHotels = [
    'LONDON' => [
        [
            'hotelId' => 'ACPAR419',
            'name' => 'London Central Hotel',
            'city' => 'LONDON',
            'roomType' => 'Standard',
            'price' => '120',
            'currency' => 'GBP',
            'checkIn' => $checkIn,
            'checkOut' => $checkOut
        ],
        [
            'hotelId' => 'TELONMFS',
            'name' => 'Tower Bridge Inn',
            'city' => 'LONDON',
            'roomType' => 'Deluxe',
            'price' => '200',
            'currency' => 'GBP',
            'checkIn' => $checkIn,
            'checkOut' => $checkOut
        ]
    ],
    'NYC' => [
        [
            'hotelId' => 'ADNYCCTB',
            'name' => 'NYC Times Square Hotel',
            'city' => 'NYC',
            'roomType' => 'Standard',
            'price' => '150',
            'currency' => 'USD',
            'checkIn' => $checkIn,
            'checkOut' => $checkOut
        ]
    ]
];

if (isset($dummyHotels[$city])) {
    echo json_encode(['success' => true, 'hotels' => $dummyHotels[$city]]);
} else {
    echo json_encode(['success' => false, 'hotels' => [], 'message' => 'No hotels available for this city in sandbox']);
}
