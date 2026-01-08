<?php
// db-columns.php - get colucm 
require_once 'config.php';

try {
    $input = file_get_contents('php://input');
    error_log("db-columns.php - Input: " . $input);
    
    $data = json_decode($input, true);

    if (!$data) {
        sendResponse(false, 'Invalid JSON payload', null);
    }

    // Extract credentials and table name
    $type = sanitizeInput($data['type'] ?? '');
    $host = sanitizeInput($data['host'] ?? '');
    $port = sanitizeInput($data['port'] ?? '');
    $database = sanitizeInput($data['database'] ?? '');
    $username = sanitizeInput($data['username'] ?? '');
    $password = $data['password'] ?? '';
    $tableName = sanitizeInput($data['table'] ?? '');

    if (!$type || !$host || !$database || !$username || !$tableName) {
        sendResponse(false, 'Missing required parameters', null);
    }

    // Validate table name
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
        sendResponse(false, 'Invalid table name', null);
    }

    // Connect
    $pdo = createConnection($type, $host, $port, $database, $username, $password);

    // Get columns
    $columns = getColumns($pdo, $type, $database, $tableName);

    if (empty($columns)) {
        sendResponse(false, 'No columns found', null);
    }

    // Get preview data
    $previewData = fetchData($pdo, $tableName, ['*'], 5);

    sendResponse(true, 'Columns loaded successfully', [
        'columns' => $columns,
        'preview' => $previewData,
        'count' => count($columns)
    ]);

} catch (Exception $e) {
    error_log("db-columns.php ERROR: " . $e->getMessage());
    sendResponse(false, 'Error: ' . $e->getMessage(), null);
}
?>