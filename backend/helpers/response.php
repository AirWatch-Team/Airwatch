<?php
/**
 * AirWatch Standardized API Response Helper
 */

/**
 * Send JSON Success Response
 */
function sendResponse(bool $success, string $message, mixed $data = null, int $statusCode = 200, array $meta = []): void {
    http_response_code($statusCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    if (!empty($meta)) {
        $response['meta'] = $meta;
    }

    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send JSON Error Response
 */
function sendError(string $message, int $statusCode = 400, array $errors = []): void {
    http_response_code($statusCode);

    $response = [
        'success' => false,
        'message' => $message,
    ];

    if (!empty($errors)) {
        $response['errors'] = $errors;
    }

    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Parse incoming JSON body or $_POST array seamlessly
 */
function getRequestData(): array {
    $rawInput = file_get_contents('php://input');
    $json = json_decode($rawInput, true);

    if (is_array($json)) {
        return array_merge($_GET, $json);
    }

    return array_merge($_GET, $_POST);
}
