<?php
/**
 * AirWatch API Endpoint: Dashboard Overview Statistics
 * Method: GET
 * Calculates high-performance SQL aggregate metrics directly in MySQL
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/aqi.php';

setCorsAndJsonHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed. Use GET request.', 405);
}

try {
    $db = Database::getConnection();

    // SQL Aggregation Query
    $statsSql = "SELECT 
                    COUNT(*) as total_records,
                    ROUND(AVG(aqi), 1) as avg_aqi,
                    MAX(aqi) as highest_aqi,
                    MIN(aqi) as lowest_aqi,
                    COUNT(DISTINCT location) as unique_locations,
                    ROUND(AVG(pm25), 2) as avg_pm25,
                    ROUND(AVG(pm10), 2) as avg_pm10
                 FROM air_quality_records";

    $statsStmt = $db->query($statsSql);
    $stats = $statsStmt->fetch();

    if ((int)$stats['total_records'] === 0) {
        sendResponse(true, 'No environmental measurement records found.', [
            'total_records'     => 0,
            'avg_aqi'           => 0,
            'highest_aqi'       => 0,
            'lowest_aqi'        => 0,
            'unique_locations'  => 0,
            'avg_pm25'          => 0,
            'avg_pm10'          => 0,
            'latest_record'     => null,
            'highest_alert'     => null
        ]);
    }

    // Fetch latest record
    $latestStmt = $db->query("SELECT * FROM air_quality_records ORDER BY record_date DESC, record_time DESC, id DESC LIMIT 1");
    $latestRecord = $latestStmt->fetch() ?: null;
    if ($latestRecord) {
        $latestRecord['aqi_details'] = getAQIDetails((int)$latestRecord['aqi']);
    }

    // Fetch highest AQI record (alert condition)
    $alertStmt = $db->query("SELECT * FROM air_quality_records ORDER BY aqi DESC, record_date DESC LIMIT 1");
    $highestAlertRecord = $alertStmt->fetch() ?: null;
    if ($highestAlertRecord) {
        $highestAlertRecord['aqi_details'] = getAQIDetails((int)$highestAlertRecord['aqi']);
    }

    // Overall Average AQI category
    $avgAqiInt = (int)round((float)$stats['avg_aqi']);
    $overallStatus = getAQIDetails($avgAqiInt);

    $response = [
        'total_records'     => (int)$stats['total_records'],
        'avg_aqi'           => (float)$stats['avg_aqi'],
        'avg_aqi_status'    => $overallStatus,
        'highest_aqi'       => (int)$stats['highest_aqi'],
        'lowest_aqi'        => (int)$stats['lowest_aqi'],
        'unique_locations'  => (int)$stats['unique_locations'],
        'avg_pm25'          => (float)$stats['avg_pm25'],
        'avg_pm10'          => (float)$stats['avg_pm10'],
        'latest_record'     => $latestRecord,
        'highest_alert'     => $highestAlertRecord
    ];

    sendResponse(true, 'Dashboard statistics loaded successfully.', $response);

} catch (PDOException $e) {
    sendError('Server error while retrieving dashboard statistics.', 500);
}
