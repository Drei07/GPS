<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Proxy server URL
$proxyServerUrl = 'https://r3garagerental.online/gps.php'; // Replace with your proxy server URL

// Database connection parameters
$host = 'localhost';
$db = 'GPS';
$user = 'root';
$pass = '';

// Fetch data from the proxy server
$response = file_get_contents($proxyServerUrl);

if ($response !== false) {
    header('Content-Type: application/json');
    echo $response;

    // Decode the JSON response
    $data = json_decode($response, true);
    error_log(print_r($data, true)); // Debugging output

    // Check if data is in array format and not empty
    if (is_array($data) && !empty($data)) {
        foreach ($data as $gps_data) {
            // Extract data for each GPS entry
            $gps_id = $gps_data['gps_id'] ?? 'N/A';
            $latitude = $gps_data['latitude'] ?? null;
            $longitude = $gps_data['longitude'] ?? null;
            $speed = $gps_data['speed'] ?? 0.0;
            $satellites = $gps_data['satellites'] ?? 0;
            $gps_status = $gps_data['gps_status'] ?? 'No Signal';
            $timestamp = date("Y-m-d H:i:s");

            if ($latitude !== null && $longitude !== null) {
                try {
                    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Check if the car is currently rented
                    $car_id = 1; // Assuming car with ID 1
                    $stmt = $pdo->prepare("SELECT status FROM car WHERE id = :car_id");
                    $stmt->bindParam(":car_id", $car_id);
                    $stmt->execute();
                    $carStatus = $stmt->fetchColumn();

                    error_log("Car status: $carStatus"); // Log the car status

                    if ($carStatus === 'RENTED') {
                        // Generate unique filename for the rental day for each GPS
                        $carName = 'VIOS';
                        $dateStr = strtoupper(date("F_d"));
                        $filename = $gps_id . "_LOG_" . strtoupper($carName) . "_{$dateStr}.json";
                        $filepath = __DIR__ . '/' . $filename;

                        // Prepare GPS data for JSON logging
                        $log_data = [
                            'gps_id' => $gps_id,
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'speed' => $speed,
                            'timestamp' => $timestamp
                        ];

                        // Append GPS data to the log file
                        file_put_contents($filepath, json_encode($log_data) . PHP_EOL, FILE_APPEND);

                        // Check if a log entry already exists for this car and date
                        $today = date("Y-m-d");
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM gps_log WHERE car_id = :car_id AND gps_id = :gps_id AND DATE(date) = :today");
                        $stmt->bindParam(":car_id", $car_id);
                        $stmt->bindParam(":gps_id", $gps_id);
                        $stmt->bindParam(":today", $today);
                        $stmt->execute();
                        $logExists = $stmt->fetchColumn();

                        // Insert into gps_log table only if no entry exists for today for the specific GPS ID
                        if ($logExists == 0) {
                            $stmt = $pdo->prepare("INSERT INTO gps_log (car_id, gps_id, filename, date) VALUES (:car_id, :gps_id, :filename, NOW())");
                            $stmt->bindParam(":car_id", $car_id);
                            $stmt->bindParam(":gps_id", $gps_id);
                            $stmt->bindParam(":filename", $filename);
                            $stmt->execute();
                            error_log("New log entry created in gps_log for $filename.");
                        } else {
                            error_log("Log entry for $filename already exists for today.");
                        }
                    } 
                } catch (PDOException $e) {
                    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
                    error_log("Database error: " . $e->getMessage());
                }
            } else {
                error_log("No valid coordinates for GPS ID: $gps_id. Coordinates were latitude: $latitude, longitude: $longitude.");
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No valid data received from the proxy server']);
        error_log("No valid data received from proxy server.");
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'gps_id' => 'N/A',
        'wifi_status' => 'No device found',
        'latitude' => 0.0,
        'longitude' => 0.0,
        'speed' => 0.0,
        'satellites' => 0,
        'gps_status' => 'No Signal'
    ]);
    error_log("Failed to fetch data from proxy server: " . error_get_last()['message']);
}
