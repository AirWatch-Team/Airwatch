<?php
/**
 * AirWatch API Endpoint: Pollution Sources Breakdown & Percentages
 * Method: GET
 * Calculates distribution percentages dynamically from database
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

setCorsAndJsonHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed. Use GET request.', 405);
}

try {
    $db = Database::getConnection();

    // Total records count query
    $totalStmt = $db->query("SELECT COUNT(*) as total FROM air_quality_records");
    $totalRecords = (int)$totalStmt->fetch()['total'];

    if ($totalRecords === 0) {
        sendResponse(true, 'No records found to calculate pollution source distribution.', []);
    }

    // SQL Aggregation by pollution source
    $sql = "SELECT 
                pollution_source,
                COUNT(*) as record_count,
                ROUND(AVG(aqi), 1) as average_aqi
            FROM air_quality_records
            GROUP BY pollution_source
            ORDER BY record_count DESC";

    $stmt = $db->query($sql);
    $sources = $stmt->fetchAll();

    foreach ($sources as &$src) {
        $count = (int)$src['record_count'];
        $src['record_count'] = $count;
        $src['percentage'] = round(($count / $totalRecords) * 100, 1);
        $src['average_aqi'] = (float)$src['average_aqi'];
    }

    sendResponse(true, 'Pollution source breakdown calculated successfully.', $sources, 200, [
        'total_records' => $totalRecords
    ]);

} catch (PDOException $e) {
    sendError('Server error while processing pollution source analysis.', 500);
}
