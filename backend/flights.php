<?php

// header("Access-Control-Allow-Origin: *");
// header("Content-Type: application/json");

function fetchAndInsertFlights(PDO $db, string $accessToken, string $origin, string $destination, string $departureDate): int
{
    $apiUrl = "https://test.api.amadeus.com/v2/shopping/flight-offers?originLocationCode=$origin&destinationLocationCode=$destination&departureDate=$departureDate&adults=1&nonStop=false&currencyCode=USD&max=20";

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Amadeus API error ($httpCode): $response");
    }

    $data = json_decode($response, true);
    $offers = $data['data'] ?? [];

    $insertedCount = 0;

    $stmt = $db->prepare("INSERT INTO flights (origin, destination, airline, flight_number, departure_time, arrival_time, duration, aircraft, price, currency) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($offers as $offer) {
    // Origin: first segment departure
    $firstSegment = $offer['itineraries'][0]['segments'][0];
    $origin = $firstSegment['departure']['iataCode'];

    // Destination: last segment arrival
    $lastItinerary = end($offer['itineraries']);
    $lastSegment = end($lastItinerary['segments']);
    $destination = $lastSegment['arrival']['iataCode'];

    // Flight number: concatenate all segments' carrierCode + number (if you want full route)
    $flightNumbers = [];
    foreach ($offer['itineraries'] as $itinerary) {
        foreach ($itinerary['segments'] as $segment) {
            $flightNumbers[] = $segment['carrierCode'] . $segment['number'];
        }
    }
    $flightNumber = implode(', ', $flightNumbers);

    // Airline: maybe from first segment carrierCode or offer['validatingAirlineCodes'][0]
    $airline = $firstSegment['carrierCode'];

    // Departure time: first segment departure time
    $departureTime = $firstSegment['departure']['at'];

    // Arrival time: last segment arrival time
    $arrivalTime = $lastSegment['arrival']['at'];

    // Duration: total duration (e.g. offer['itineraries'][0]['duration'])
    $duration = $offer['itineraries'][0]['duration'];

    // Aircraft: maybe from first segment ['aircraft']['code'] if available
    $aircraft = $firstSegment['aircraft']['code'] ?? null;

    // Price: from offer['price']['total']
    $price = $offer['price']['total'] ?? null;

    // Currency: from offer['price']['currency']
    $currency = $offer['price']['currency'] ?? 'USD';

    // Insert into DB here

$stmt->execute([
    $origin,
    $destination,
    $airline,
    $flightNumber,
    $departureTime,
    $arrivalTime,
    $duration,
    $aircraft,
    $price,
    $currency
]);

$insertedCount++;
    }

    return $insertedCount;
}
