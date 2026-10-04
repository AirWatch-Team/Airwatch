<?php
/**
 * AirWatch API Endpoint: Delete Air Quality Record
 * Method: POST or DELETE
 */

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/response.php';

setCorsAndJsonHeaders();

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST' && $method !== 'DELETE') {
    sendError('Method not allowed. Use POST or DELETE request.', 405);
}

$inputData = getRequestData();
$recordId = (int)($inputData['id'] ?? 0);

if ($recordId <= 0) {
    sendError('Valid record ID is required for deletion.', 400);
}

try {
    $db = Database::getConnection();

    // Check if record exists
    $checkStmt = $db->prepare("SELECT id, location, aqi FROM air_quality_records WHERE id = :id");
    $checkStmt->execute([':id' => $recordId]);
    $record = $checkStmt->fetch();

    if (!$record) {
        sendError('Record not found or already deleted.', 404);
    }

    $deleteStmt = $db->prepare("DELETE FROM air_quality_records WHERE id = :id");
    $deleteStmt->execute([':id' => $recordId]);

    sendResponse(true, "Record #{$recordId} from {$record['location']} deleted successfully.", [
        'deleted_id' => $recordId
    ]);

} catch (PDOException $e) {
    sendError('Server error while deleting record from database.', 500);
}
