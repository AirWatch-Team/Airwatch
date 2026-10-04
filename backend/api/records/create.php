<?php
/**
 * AirWatch API Endpoint: Create Air Quality Record
 * Method: POST
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/validation.php';
require_once __DIR__ . '/../../helpers/aqi.php';

setCorsAndJsonHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST request.', 405);
}

$inputData = getRequestData();
$validation = validateRecordInput($inputData);

if (!$validation['is_valid']) {
    sendError('Validation failed. Please check your inputs.', 400, $validation['errors']);
}

$data = $validation['sanitized'];

// Calculate server-side AQI status to guarantee consistency
$aqiStatus = getAQIStatus($data['aqi']);

try {
    $db = Database::getConnection();
    
    $query = "INSERT INTO air_quality_records 
              (location, record_date, record_time, aqi, pm25, pm10, temperature, humidity, pollution_source, notes, aqi_status) 
              VALUES 
              (:location, :record_date, :record_time, :aqi, :pm25, :pm10, :temperature, :humidity, :pollution_source, :notes, :aqi_status)";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindValue(':location', $data['location']);
    $stmt->bindValue(':record_date', $data['record_date']);
    $stmt->bindValue(':record_time', $data['record_time']);
    $stmt->bindValue(':aqi', $data['aqi'], PDO::PARAM_INT);
    $stmt->bindValue(':pm25', $data['pm25']);
    $stmt->bindValue(':pm10', $data['pm10']);
    $stmt->bindValue(':temperature', $data['temperature']);
    $stmt->bindValue(':humidity', $data['humidity']);
    $stmt->bindValue(':pollution_source', $data['pollution_source']);
    $stmt->bindValue(':notes', $data['notes']);
    $stmt->bindValue(':aqi_status', $aqiStatus);

    $stmt->execute();
    $recordId = (int)$db->lastInsertId();

    // Fetch newly created record
    $fetchStmt = $db->prepare("SELECT * FROM air_quality_records WHERE id = :id");
    $fetchStmt->execute([':id' => $recordId]);
    $newRecord = $fetchStmt->fetch();
    
    // Attach detailed AQI metadata
    $newRecord['aqi_details'] = getAQIDetails((int)$newRecord['aqi']);

    sendResponse(true, 'Air quality measurement record created successfully.', $newRecord, 201);

} catch (PDOException $e) {
    sendError('Server error while saving record to database.', 500);
}
