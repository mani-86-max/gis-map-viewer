<?php
// db-tables.php - List tables 
require_once 'config.php';

try {
    // Read JSON body
    $input = file_get_contents('php://input');
    error_log("db-tables.php - Received input: " . $input);
    
    $data = json_decode($input, true);

    if (!$data) {
        error_log("db-tables.php - No JSON data received");
        sendResponse(false, 'Invalid JSON payload', null);
    }

    // Extract credentials
    $type = sanitizeInput($data['type'] ?? '');
    $host = sanitizeInput($data['host'] ?? '');
    $port = sanitizeInput($data['port'] ?? '');
    $database = sanitizeInput($data['database'] ?? '');
    $username = sanitizeInput($data['username'] ?? '');
    $password = $data['password'] ?? '';

    error_log("db-tables.php - Credentials: type=$type, host=$host, db=$database, user=$username");

    if (!$type || !$host || !$database || !$username) {
        error_log("db-tables.php - Missing credentials");
        sendResponse(false, 'Missing database credentials', null);
    }

    // Connect
    error_log("db-tables.php - Attempting connection...");
    $pdo = createConnection($type, $host, $port, $database, $username, $password);
    error_log("db-tables.php - Connection successful");

    // Query tables
    if ($type === 'mysql') {
        $sql = "
            SELECT 
                TABLE_NAME,
                TABLE_ROWS
            FROM information_schema.tables
            WHERE table_schema = :db
            AND table_type = 'BASE TABLE'
            ORDER BY TABLE_NAME
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['db' => $database]);
    }
    else if ($type === 'postgresql' || $type === 'pgsql') {
        $sql = "
            SELECT
                tablename AS TABLE_NAME,
                NULL AS TABLE_ROWS
            FROM pg_tables
            WHERE schemaname = 'public'
            ORDER BY tablename
        ";

        $stmt = $pdo->query($sql);
    }
    else {
        error_log("db-tables.php - Unsupported type: $type");
        sendResponse(false, 'Unsupported database type', null);
    }

    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("db-tables.php - Found " . count($tables) . " tables");
    error_log("db-tables.php - Tables: " . print_r($tables, true));

    if (empty($tables)) {
        sendResponse(false, 'No tables found in database', null);
    }

    sendResponse(true, 'Tables retrieved successfully', [
        'tables' => $tables,
        'count' => count($tables)
    ]);

} catch (Exception $e) {
    error_log("db-tables.php ERROR: " . $e->getMessage());
    error_log("db-tables.php TRACE: " . $e->getTraceAsString());
    sendResponse(false, 'Error: ' . $e->getMessage(), null);
}
?>