<?php
/**
 * AirWatch Database Configuration & Central Connection Manager
 * 
 * Uses PDO with prepared statements, error exception mode, and proper UTF-8 charset.
 */

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'airwatch');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

class Database {
    private static ?PDO $instance = null;

    /**
     * Get singleton PDO Connection
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4", DB_HOST, DB_PORT, DB_NAME);
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Return clean JSON error response without revealing stack traces
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Database connection failed. Please ensure MySQL is running and the database is imported.',
                    'error_code' => 'DB_CONN_ERROR'
                ]);
                exit;
            }
        }

        return self::$instance;
    }
}

/**
 * Global helper to set standard API Headers (CORS & JSON Content-Type)
 */
function setCorsAndJsonHeaders(): void {
    // Set JSON Content-Type
    header('Content-Type: application/json; charset=utf-8');
    
    // CORS configuration for local development / Apache XAMPP
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
