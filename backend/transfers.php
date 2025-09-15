<?php

function fetchAndInsertTransfers(PDO $db, string $accessToken): int
{
    $apiUrl = "https://test.api.amadeus.com/v1/shopping/transfer-offers";

    // Prepare the request body
    $requestBody = [
        "startLocationCode" => "CDG",  // Starting location (e.g., Charles de Gaulle)
        "endLocationCode" => "PAR",    // End location (e.g., Paris City Center)
        "startDateTime" => date('Y-m-d\TH:i:s', strtotime('+1 day 10:00')),  // Start time of the transfer
        "transferType" => "PRIVATE",   // Type of transfer (e.g., PRIVATE)
        "passengers" => 1,             // Number of passengers
    ];

    // Convert the body to JSON
    $jsonData = json_encode($requestBody);

    // Initialize the cURL request
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonData
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Amadeus API error ($httpCode): $response");
    }

    // Decode the JSON response
    $data = json_decode($response, true);
    $transfers = $data['data'] ?? [];

    if (empty($transfers)) {
        throw new Exception("No transfer data found in the API response.");
    }

    $insertedCount = 0;

    // Prepare the SQL statement to insert into the database
    $stmt = $db->prepare("INSERT INTO transfers (provider, vehicle_type, start_location, end_location, price, currency, duration, booking_link, passengers, transfer_type, date_time)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($transfers as $transfer) {
        // Extract relevant data from the transfer response
        $provider = $transfer['serviceProvider']['name'] ?? 'Unknown Provider';
        // $vehicleType = $transfer['vehicle']['description'] ?? 'Standard';
        $vehicleType = substr($transfer['vehicle']['description'] ?? 'Standard', 0, 100);  // Limit to 100 characters
        $startLocation = $transfer['start']['locationCode'] ?? 'Unknown Start';
        $endLocation = $transfer['end']['locationCode'] ?? 'Unknown End';
        $price = $transfer['quotation']['monetaryAmount'] ?? 0.00;
        $currency = $transfer['quotation']['currencyCode'] ?? 'USD';
        $duration = calculateDuration($transfer['start']['dateTime'], $transfer['end']['dateTime']);
        $bookingLink = $transfer['serviceProvider']['termsUrl'] ?? '#'; // Using the provider's terms URL as the booking link
        $passengers = 1; // Assuming 1 passenger as a default, adjust as needed
        $transferType = $transfer['transferType'] ?? 'UNKNOWN';  // Type of transfer (e.g., "PRIVATE", "SHARED")
        $dateTime = $transfer['start']['dateTime'] ?? date('Y-m-d H:i:s');  // Transfer start date and time

        // Insert data into the database
        $stmt->execute([
            $provider,
            $vehicleType,
            $startLocation,
            $endLocation,
            $price,
            $currency,
            $duration,
            $bookingLink,
            $passengers,
            $transferType,
            $dateTime
        ]);

        $insertedCount++;
    }

    return $insertedCount;
}

// Calculate the duration in hours and minutes
function calculateDuration($startTime, $endTime)
{
    $start = new DateTime($startTime);
    $end = new DateTime($endTime);
    $interval = $start->diff($end);
    return $interval->format('%h hours %i minutes');
}
