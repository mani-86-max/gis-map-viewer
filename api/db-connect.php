<?php
// db-connect.php - Test Database Connection

require_once 'config.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        sendResponse(false, 'Invalid JSON data', null);
    }
    
    $type = sanitizeInput($data['type'] ?? '');
    $host = sanitizeInput($data['host'] ?? '');
    $port = sanitizeInput($data['port'] ?? '');
    $database = sanitizeInput($data['database'] ?? '');
    $username = sanitizeInput($data['username'] ?? '');
    $password = $data['password'] ?? '';
    
    $errors = validateCredentials($host, $port, $database, $username);
    if (!empty($errors)) {
        sendResponse(false, implode(', ', $errors), null);
    }
    
    $result = testConnection($type, $host, $port, $database, $username, $password);
    
    if ($result['success']) {
        session_start();
        $_SESSION['db_config'] = [
            'type' => $type,
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'connected_at' => time()
        ];
        
        sendResponse(true, $result['message'], [
            'db_type' => $result['db_type'],
            'server_version' => $result['server_version']
        ]);
    } else {
        sendResponse(false, $result['message'], null);
    }
    
} catch (Exception $e) {
    sendResponse(false, 'Error: ' . $e->getMessage(), null);
}
?>