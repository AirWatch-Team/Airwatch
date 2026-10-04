<?php
/**
 * AirWatch API Endpoint: Export Environmental Records as CSV
 * Method: GET
 * Generates downloadable CSV stream directly from MySQL
 */

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed. Use GET request.', 405);
}

try {
    $db = Database::getConnection();

    // Build filter if provided
    $where = [];
    $params = [];

    if (!empty($_GET['location'])) {
        $where[] = "location = :location";
        $params[':location'] = trim($_GET['location']);
    }
    if (!empty($_GET['start_date'])) {
        $where[] = "record_date >= :start_date";
        $params[':start_date'] = $_GET['start_date'];
    }
    if (!empty($_GET['end_date'])) {
        $where[] = "record_date <= :end_date";
        $params[':end_date'] = $_GET['end_date'];
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT id, location, record_date, record_time, aqi, aqi_status, pm25, pm10, temperature, humidity, pollution_source, notes, created_at 
            FROM air_quality_records $whereClause ORDER BY record_date DESC, record_time DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    // Stream CSV headers
    $filename = "airwatch_environmental_data_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // CSV Header row
    fputcsv($output, [
        'Record ID',
        'Location',
        'Date',
        'Time',
        'AQI',
        'AQI Status',
        'PM2.5 (µg/m³)',
        'PM10 (µg/m³)',
        'Temperature (°C)',
        'Humidity (%)',
        'Pollution Source',
        'Notes',
        'Created At'
    ]);

    // CSV Rows
    foreach ($records as $row) {
        fputcsv($output, [
            $row['id'],
            $row['location'],
            $row['record_date'],
            $row['record_time'],
            $row['aqi'],
            $row['aqi_status'],
            $row['pm25'],
            $row['pm10'],
            $row['temperature'] ?? 'N/A',
            $row['humidity'] ?? 'N/A',
            $row['pollution_source'],
            $row['notes'] ?? '',
            $row['created_at']
        ]);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    sendError('Server error while exporting CSV.', 500);
}
