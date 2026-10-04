<?php
/**
 * AirWatch API Endpoint: Location-wise Environmental Metrics & Comparison
 * Method: GET
 * Uses SQL GROUP BY aggregation
 */

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/aqi.php';

setCorsAndJsonHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed. Use GET request.', 405);
}

try {
    $db = Database::getConnection();

    // Query statistics grouped by location
    $sql = "SELECT 
                location,
                COUNT(*) as record_count,
                ROUND(AVG(aqi), 1) as average_aqi,
                MAX(aqi) as highest_aqi,
                MIN(aqi) as lowest_aqi,
                ROUND(AVG(pm25), 2) as average_pm25,
                ROUND(AVG(pm10), 2) as average_pm10,
                MAX(record_date) as latest_record_date
            FROM air_quality_records
            GROUP BY location
            ORDER BY average_aqi DESC";

    $stmt = $db->query($sql);
    $locationStats = $stmt->fetchAll();

    foreach ($locationStats as &$loc) {
        $loc['record_count'] = (int)$loc['record_count'];
        $loc['average_aqi'] = (float)$loc['average_aqi'];
        $loc['highest_aqi'] = (int)$loc['highest_aqi'];
        $loc['lowest_aqi'] = (int)$loc['lowest_aqi'];
        $loc['average_pm25'] = (float)$loc['average_pm25'];
        $loc['average_pm10'] = (float)$loc['average_pm10'];
        $loc['aqi_details'] = getAQIDetails((int)round($loc['average_aqi']));
    }

    sendResponse(true, 'Location analysis calculated successfully.', $locationStats);

} catch (PDOException $e) {
    sendError('Server error while processing location analysis.', 500);
}
