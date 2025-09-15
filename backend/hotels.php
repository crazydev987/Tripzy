<?php
function fetchAndInsertHotels($db, $token, $hotelIds, $checkIn, $checkOut)
{
    $url = "https://test.api.amadeus.com/v3/shopping/hotel-offers";

    // We'll only send up to 50 hotelIds per request to avoid provider errors
    $batchSize = 50;
    $insertedCount = 0;

    for ($i = 0; $i < count($hotelIds); $i += $batchSize) {
        $batchIds = array_slice($hotelIds, $i, $batchSize);
        $query = http_build_query([
            'hotelIds' => implode(',', $batchIds),
            'adults' => 1,
            'checkInDate' => $checkIn,
            'checkOutDate' => $checkOut,
            'bestRateOnly' => true
        ]);

        $headers = [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ];

        $ch = curl_init("$url?$query");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception("Curl error: " . curl_error($ch));
        }
        curl_close($ch);

        $data = json_decode($response, true);

        // Skip batches with errors
        if (!isset($data['data'])) {
            continue;
        }

        $stmt = $db->prepare("INSERT INTO hotels (hotel_name, city, description, room_type, price, check_in_date, check_out_date, rating)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($data['data'] as $hotel) {
            $hotelName = $hotel['hotel']['name'] ?? 'N/A';
            $city = $hotel['hotel']['address']['cityName'] ?? 'N/A';
            $description = $hotel['hotel']['description']['text'] ?? 'No description';
            $rating = $hotel['hotel']['rating'] ?? 0;

            foreach ($hotel['offers'] as $offer) {
                $roomType = $offer['room']['typeEstimated']['category'] ?? 'Standard';
                $price = $offer['price']['total'] ?? 0.00;

                $stmt->execute([
                    $hotelName,
                    $city,
                    $description,
                    $roomType,
                    $price,
                    $checkIn,
                    $checkOut,
                    $rating
                ]);

                $insertedCount++;
            }
        }
    }

    return $insertedCount;
}
