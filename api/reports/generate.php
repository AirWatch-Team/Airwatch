<?php
/**
 * AirWatch API Endpoint: Environmental Comprehensive Report Data
 * Method: GET
 * Aggregates complete database facts for print & web reporting
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

    // 1. Overall stats
    $statsStmt = $db->query("SELECT 
                                COUNT(*) as total_records,
                                ROUND(AVG(aqi), 1) as avg_aqi,
                                MAX(aqi) as highest_aqi,
                                MIN(aqi) as lowest_aqi,
                                COUNT(DISTINCT location) as locations_monitored,
                                ROUND(AVG(pm25), 2) as avg_pm25,
                                ROUND(AVG(pm10), 2) as avg_pm10
                             FROM air_quality_records");
    $stats = $statsStmt->fetch();
    $totalRecords = (int)$stats['total_records'];

    if ($totalRecords === 0) {
        sendResponse(true, 'No records available to generate environmental report.', [
            'has_data' => false,
            'message'  => 'Database has no measurement records.'
        ]);
    }

    // 2. Most Polluted & Least Polluted Locations
    $locStmt = $db->query("SELECT location, ROUND(AVG(aqi), 1) as avg_aqi 
                           FROM air_quality_records 
                           GROUP BY location 
                           ORDER BY avg_aqi DESC");
    $locs = $locStmt->fetchAll();

    $mostPolluted = $locs[0] ?? null;
    $leastPolluted = !empty($locs) ? $locs[count($locs) - 1] : null;

    // 3. Most Common Pollution Source
    $srcStmt = $db->query("SELECT pollution_source, COUNT(*) as cnt 
                           FROM air_quality_records 
                           GROUP BY pollution_source 
                           ORDER BY cnt DESC LIMIT 1");
    $topSource = $srcStmt->fetch();

    // 4. Alert Records (AQI >= 151)
    $alertsStmt = $db->query("SELECT id, location, record_date, aqi, aqi_status, pollution_source 
                              FROM air_quality_records 
                              WHERE aqi >= 151 
                              ORDER BY aqi DESC LIMIT 5");
    $alerts = $alertsStmt->fetchAll();
    foreach ($alerts as &$a) {
        $a['aqi_details'] = getAQIDetails((int)$a['aqi']);
    }

    // 5. Automated Data-Driven Insights Generator
    $insights = [];

    if ($mostPolluted) {
        $insights[] = "{$mostPolluted['location']} currently records the highest average AQI ({$mostPolluted['avg_aqi']}) among all monitored sites.";
    }
    if ($leastPolluted && $leastPolluted['location'] !== ($mostPolluted['location'] ?? '')) {
        $insights[] = "{$leastPolluted['location']} maintains the cleanest ambient air quality with an average AQI of {$leastPolluted['avg_aqi']}.";
    }
    if ($topSource) {
        $pct = round(($topSource['cnt'] / $totalRecords) * 100, 1);
        $insights[] = "{$topSource['pollution_source']} is the dominant recorded pollution contributor, accounting for {$pct}% of all logged observations.";
    }

    $avgAqiNum = (float)$stats['avg_aqi'];
    if ($avgAqiNum <= 50) {
        $insights[] = "Overall system-wide average AQI ({$avgAqiNum}) remains within healthy, satisfactory limits.";
    } elseif ($avgAqiNum <= 100) {
        $insights[] = "Overall system-wide average AQI ({$avgAqiNum}) is Moderate. Routine monitoring is recommended for sensitive groups.";
    } else {
        $insights[] = "Overall system-wide average AQI ({$avgAqiNum}) indicates elevated pollution levels. Targeted mitigation policies are advised.";
    }

    $reportData = [
        'has_data'              => true,
        'report_generated_at'   => date('F j, Y, g:i a'),
        'total_records'         => $totalRecords,
        'locations_monitored'   => (int)$stats['locations_monitored'],
        'avg_aqi'               => (float)$stats['avg_aqi'],
        'avg_aqi_details'       => getAQIDetails((int)round($stats['avg_aqi'])),
        'highest_aqi'           => (int)$stats['highest_aqi'],
        'lowest_aqi'            => (int)$stats['lowest_aqi'],
        'most_polluted_location'=> $mostPolluted,
        'least_polluted_location'=> $leastPolluted,
        'most_common_source'    => $topSource['pollution_source'] ?? 'N/A',
        'avg_pm25'              => (float)$stats['avg_pm25'],
        'avg_pm10'              => (float)$stats['avg_pm10'],
        'insights'              => $insights,
        'alerts'                => $alerts
    ];

    sendResponse(true, 'Environmental report data generated successfully.', $reportData);

} catch (PDOException $e) {
    sendError('Server error while generating environmental report.', 500);
}
