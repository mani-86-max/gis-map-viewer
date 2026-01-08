<?php
// db-fetch-data from table
require_once 'config.php';

try {
    $input = file_get_contents('php://input');
    error_log("db-fetch-data.php - Input: " . substr($input, 0, 500));
    
    $data = json_decode($input, true);

    if (!$data) {
        sendResponse(false, 'Invalid JSON payload', null);
    }

    // Extract all required data
    $type = sanitizeInput($data['type'] ?? '');
    $host = sanitizeInput($data['host'] ?? '');
    $port = sanitizeInput($data['port'] ?? '');
    $database = sanitizeInput($data['database'] ?? '');
    $username = sanitizeInput($data['username'] ?? '');
    $password = $data['password'] ?? '';
    
    $tableName = sanitizeInput($data['table'] ?? '');
    $latColumn = sanitizeInput($data['latColumn'] ?? '');
    $lngColumn = sanitizeInput($data['lngColumn'] ?? '');
    $nameColumn = sanitizeInput($data['nameColumn'] ?? '');
    $limit = isset($data['limit']) ? (int)$data['limit'] : MAX_ROWS;

    error_log("db-fetch-data.php - Table: $tableName, Lat: $latColumn, Lng: $lngColumn");

    if (!$type || !$host || !$database || !$username) {
        sendResponse(false, 'Missing database credentials', null);
    }

    if (empty($tableName) || empty($latColumn) || empty($lngColumn)) {
        sendResponse(false, 'Table name and coordinate columns are required', null);
    }

    // Connect
    $pdo = createConnection($type, $host, $port, $database, $username, $password);

    // Fetch data
    $rows = fetchData($pdo, $tableName, ['*'], $limit);

    if (empty($rows)) {
        sendResponse(false, 'No data found in table', null);
    }

    // Validate coordinates
    $validRows = [];
    $errorCount = 0;

    foreach ($rows as $row) {
        $lat = isset($row[$latColumn]) ? floatval($row[$latColumn]) : null;
        $lng = isset($row[$lngColumn]) ? floatval($row[$lngColumn]) : null;

        if ($lat !== null && $lng !== null && 
            $lat >= -90 && $lat <= 90 && 
            $lng >= -180 && $lng <= 180) {
            $validRows[] = $row;
        } else {
            $errorCount++;
        }
    }

    if (empty($validRows)) {
        sendResponse(false, 'No valid coordinates found', null);
    }

    error_log("db-fetch-data.php - Valid rows: " . count($validRows));

    sendResponse(true, 'Data fetched successfully', [
        'rows' => $validRows,
        'total_rows' => count($rows),
        'valid_rows' => count($validRows),
        'invalid_rows' => $errorCount,
        'lat_column' => $latColumn,
        'lng_column' => $lngColumn,
        'name_column' => $nameColumn
    ]);

} catch (Exception $e) {
    error_log("db-fetch-data.php ERROR: " . $e->getMessage());
    sendResponse(false, 'Error: ' . $e->getMessage(), null);
}
?>