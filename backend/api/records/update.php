<?php
/**
 * AirWatch API Endpoint: Update Air Quality Record
 * Method: POST or PUT
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/validation.php';
require_once __DIR__ . '/../../helpers/aqi.php';

setCorsAndJsonHeaders();

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST' && $method !== 'PUT') {
    sendError('Method not allowed. Use POST or PUT request.', 405);
}

$inputData = getRequestData();

// Verify record ID exists
$recordId = (int)($inputData['id'] ?? 0);
if ($recordId <= 0) {
    sendError('Valid record ID is required for update.', 400);
}

// Validate input data
$validation = validateRecordInput($inputData);
if (!$validation['is_valid']) {
    sendError('Validation failed. Please check your inputs.', 400, $validation['errors']);
}

$data = $validation['sanitized'];

// Calculate server-side AQI status
$aqiStatus = getAQIStatus($data['aqi']);

try {
    $db = Database::getConnection();

    // Check if record exists
    $checkStmt = $db->prepare("SELECT id FROM air_quality_records WHERE id = :id");
    $checkStmt->execute([':id' => $recordId]);
    if (!$checkStmt->fetch()) {
        sendError('Record not found.', 404);
    }

    $query = "UPDATE air_quality_records SET 
              location = :location,
              record_date = :record_date,
              record_time = :record_time,
              aqi = :aqi,
              pm25 = :pm25,
              pm10 = :pm10,
              temperature = :temperature,
              humidity = :humidity,
              pollution_source = :pollution_source,
              notes = :notes,
              aqi_status = :aqi_status
              WHERE id = :id";

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
    $stmt->bindValue(':id', $recordId, PDO::PARAM_INT);

    $stmt->execute();

    // Fetch updated record
    $fetchStmt = $db->prepare("SELECT * FROM air_quality_records WHERE id = :id");
    $fetchStmt->execute([':id' => $recordId]);
    $updatedRecord = $fetchStmt->fetch();
    $updatedRecord['aqi_details'] = getAQIDetails((int)$updatedRecord['aqi']);

    sendResponse(true, 'Air quality record updated successfully.', $updatedRecord);

} catch (PDOException $e) {
    sendError('Server error while updating database record.', 500);
}
