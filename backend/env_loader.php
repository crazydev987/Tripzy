<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function loadEnv($filepath)
{
    if (!file_exists($filepath)) {
        throw new Exception(".env file not found at $filepath");
    }

    $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function getAccessToken()
{
    $clientId = getenv('AMADEUS_CLIENT_ID');
    $clientSecret = getenv('AMADEUS_CLIENT_SECRET');

    if (!$clientId || !$clientSecret) {
        throw new Exception("AMADEUS_CLIENT_ID or AMADEUS_CLIENT_SECRET missing in environment.");
    }

    $tokenFile = __DIR__ . '/amadeus_token.json';

    if (file_exists($tokenFile)) {
        $tokenData = json_decode(file_get_contents($tokenFile), true);
        if (isset($tokenData['access_token'], $tokenData['expires_at']) && time() < $tokenData['expires_at']) {
            return $tokenData['access_token'];
        }
    }

    // Request new token
    $tokenUrl = 'https://test.api.amadeus.com/v1/security/oauth2/token';
    $postData = http_build_query([
        'grant_type'    => 'client_credentials',
        'client_id'     => $clientId,
        'client_secret' => $clientSecret
    ]);

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('cURL error: ' . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Amadeus API error ($httpCode): $response");
    }

    $tokenData = json_decode($response, true);
    $tokenData['expires_at'] = time() + $tokenData['expires_in'] - 60; // buffer
    file_put_contents($tokenFile, json_encode($tokenData, JSON_PRETTY_PRINT));

    return $tokenData['access_token'];
}
