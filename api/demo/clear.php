<?php
/**
 * AirWatch API Endpoint: Clear Database Records / Clear Demo Data
 * Method: POST
 */

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/response.php';

setCorsAndJsonHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST request.', 405);
}

try {
    $db = Database::getConnection();

    // Delete all records or TRUNCATE
    $countStmt = $db->query("SELECT COUNT(*) as total FROM air_quality_records");
    $total = (int)$countStmt->fetch()['total'];

    $db->exec("TRUNCATE TABLE air_quality_records");

    sendResponse(true, "Cleared {$total} records from the database successfully.", [
        'cleared_count' => $total
    ]);

} catch (PDOException $e) {
    sendError('Server error while clearing database records.', 500);
}
