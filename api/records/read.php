<?php
/**
 * AirWatch API Endpoint: Read Air Quality Records
 * Method: GET
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

    // Check for single record lookup by ID
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $stmt = $db->prepare("SELECT * FROM air_quality_records WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['id']]);
        $record = $stmt->fetch();

        if (!$record) {
            sendError('Record not found.', 404);
        }

        $record['aqi_details'] = getAQIDetails((int)$record['aqi']);
        sendResponse(true, 'Record retrieved successfully.', $record);
    }

    // Build filtered query dynamically using prepared statements
    $where = [];
    $params = [];

    // Filter by location
    if (!empty($_GET['location'])) {
        $where[] = "location = :location";
        $params[':location'] = trim($_GET['location']);
    }

    // Filter by start date
    if (!empty($_GET['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['start_date'])) {
        $where[] = "record_date >= :start_date";
        $params[':start_date'] = $_GET['start_date'];
    }

    // Filter by end date
    if (!empty($_GET['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['end_date'])) {
        $where[] = "record_date <= :end_date";
        $params[':end_date'] = $_GET['end_date'];
    }

    // Filter by AQI status
    if (!empty($_GET['status'])) {
        $where[] = "aqi_status = :status";
        $params[':status'] = trim($_GET['status']);
    }

    // Search keyword across location, pollution source, notes
    if (!empty($_GET['search'])) {
        $where[] = "(location LIKE :search OR pollution_source LIKE :search OR notes LIKE :search)";
        $params[':search'] = '%' . trim($_GET['search']) . '%';
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Count total matching records for pagination
    $countSql = "SELECT COUNT(*) as total FROM air_quality_records $whereClause";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetch()['total'];

    // Pagination setup
    $limitParam = $_GET['limit'] ?? '10';
    $isAll = ($limitParam === '-1' || strtolower($limitParam) === 'all');
    
    $limit = $isAll ? $totalRecords : max(1, min(100, (int)$limitParam));
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;
    $totalPages = $limit > 0 ? (int)ceil($totalRecords / $limit) : 1;

    // Sorting
    $allowedSorts = [
        'date_desc' => 'record_date DESC, record_time DESC',
        'date_asc'  => 'record_date ASC, record_time ASC',
        'aqi_desc'   => 'aqi DESC',
        'aqi_asc'    => 'aqi ASC',
        'location'   => 'location ASC, record_date DESC'
    ];
    $sortKey = $_GET['sort'] ?? 'date_desc';
    $orderBy = $allowedSorts[$sortKey] ?? 'record_date DESC, record_time DESC';

    // Query records
    $query = "SELECT * FROM air_quality_records $whereClause ORDER BY $orderBy";
    if (!$isAll) {
        $query .= " LIMIT :limit OFFSET :offset";
    }

    $stmt = $db->prepare($query);
    
    foreach ($params as $param => $val) {
        $stmt->bindValue($param, $val);
    }
    
    if (!$isAll) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }

    $stmt->execute();
    $records = $stmt->fetchAll();

    // Attach AQI metadata
    foreach ($records as &$rec) {
        $rec['aqi_details'] = getAQIDetails((int)$rec['aqi']);
    }

    $meta = [
        'total_records' => $totalRecords,
        'page'          => $page,
        'limit'         => $limit,
        'total_pages'   => $totalPages
    ];

    sendResponse(true, 'Records retrieved successfully.', $records, 200, $meta);

} catch (PDOException $e) {
    sendError('Server error while reading records from database.', 500);
}
