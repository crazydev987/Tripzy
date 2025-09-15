<?php
header('Content-Type: application/json');
require_once 'env_loader.php';
loadEnv(__DIR__ . '/.env');

$city = isset($_GET['city']) ? $_GET['city'] : '';
if (!$city) {
    echo json_encode([]);
    exit;
}

$token = getAccessToken();
$cityUrl = "https://test.api.amadeus.com/v1/reference-data/locations/cities";
$query = http_build_query(['keyword' => $city]);

$ch = curl_init("$cityUrl?$query");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Authorization: Bearer $token"]
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (isset($data['data'][0]['iataCode'])) {
    echo json_encode(['cityCode' => $data['data'][0]['iataCode']]);
} else {
    echo json_encode([]);
}
