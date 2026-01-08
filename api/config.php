<?php
// config.php - Database Configuration & Helper Functions
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php-errors.log');

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Security: Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    sendResponse(false, 'Only POST requests allowed', null);
    exit();
}

// Maximum rows to fetch
define('MAX_ROWS', 10000);
define('DB_TIMEOUT', 10);

//send jason
function sendResponse($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

//sanitize output
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

//validate crediationals
function validateCredentials($host, $port, $database, $username) {
    $errors = [];
    
    if (empty($host)) $errors[] = 'Host is required';
    if (empty($port) || !is_numeric($port)) $errors[] = 'Valid port number is required';
    if (empty($database)) $errors[] = 'Database name is required';
    if (empty($username)) $errors[] = 'Username is required';
    
    $dangerous = ['--', ';', '/*', '*/', 'xp_', 'sp_', 'DROP', 'INSERT', 'DELETE', 'UPDATE'];
    foreach ($dangerous as $pattern) {
        if (stripos($database, $pattern) !== false) {
            $errors[] = 'Invalid database name';
            break;
        }
    }
    
    return $errors;
}

// Test connection
 
function testConnection($type, $host, $port, $database, $username, $password) {
    try {
        $dsn = '';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => DB_TIMEOUT,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        if ($type === 'mysql') {
            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        } elseif ($type === 'postgresql') {
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        } else {
            return ['success' => false, 'message' => 'Unsupported database type'];
        }
        
        $pdo = new PDO($dsn, $username, $password, $options);
        $stmt = $pdo->query('SELECT 1');
        
        return [
            'success' => true,
            'message' => 'Connection successful!',
            'db_type' => $type,
            'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
        ];
        
    } catch (PDOException $e) {
        $errorMsg = 'Connection failed: ';
        
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            $errorMsg .= 'Invalid username or password';
        } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
            $errorMsg .= 'Database does not exist';
        } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
            $errorMsg .= 'Cannot connect to server. Check host and port';
        } elseif (strpos($e->getMessage(), 'timed out') !== false) {
            $errorMsg .= 'Connection timeout';
        } else {
            $errorMsg .= $e->getMessage();
        }
        
        return ['success' => false, 'message' => $errorMsg];
    }
}

// Create connection
function createConnection($type, $host, $port, $database, $username, $password) {
    try {
        $dsn = '';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => DB_TIMEOUT,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        if ($type === 'mysql') {
            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        } elseif ($type === 'postgresql') {
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        }
        
        return new PDO($dsn, $username, $password, $options);
        
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}

//get tables
function getTables($pdo, $type, $database) {
    try {
        if ($type === 'mysql') {
            $stmt = $pdo->prepare("
                SELECT TABLE_NAME, TABLE_ROWS, TABLE_TYPE
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = :database
                AND TABLE_TYPE = 'BASE TABLE'
                ORDER BY TABLE_NAME
            ");
            $stmt->execute(['database' => $database]);
        } elseif ($type === 'postgresql') {
            $stmt = $pdo->prepare("
                SELECT table_name as TABLE_NAME, 
                       NULL as TABLE_ROWS,
                       'BASE TABLE' as TABLE_TYPE
                FROM information_schema.tables
                WHERE table_schema = 'public'
                AND table_type = 'BASE TABLE'
                ORDER BY table_name
            ");
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        throw new Exception('Failed to fetch tables: ' . $e->getMessage());
    }
}

//get columns
function getColumns($pdo, $type, $database, $tableName) {
    try {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new Exception('Invalid table name');
        }
        
        if ($type === 'mysql') {
            $stmt = $pdo->prepare("
                SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = :database
                AND TABLE_NAME = :table
                ORDER BY ORDINAL_POSITION
            ");
            $stmt->execute(['database' => $database, 'table' => $tableName]);
        } elseif ($type === 'postgresql') {
            $stmt = $pdo->prepare("
                SELECT column_name as COLUMN_NAME, 
                       data_type as DATA_TYPE,
                       is_nullable as IS_NULLABLE,
                       column_default as COLUMN_DEFAULT
                FROM information_schema.columns
                WHERE table_name = :table
                ORDER BY ordinal_position
            ");
            $stmt->execute(['table' => $tableName]);
        }
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        throw new Exception('Failed to fetch columns: ' . $e->getMessage());
    }
}

//fetch data
function fetchData($pdo, $tableName, $columns = ['*'], $limit = MAX_ROWS) {
    try {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new Exception('Invalid table name');
        }
        
        $limit = min((int)$limit, MAX_ROWS);
        
        if ($columns === ['*'] || empty($columns)) {
            $columnList = '*';
        } else {
            $validColumns = [];
            foreach ($columns as $col) {
                if (preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                    $validColumns[] = $col;
                }
            }
            $columnList = empty($validColumns) ? '*' : implode(', ', $validColumns);
        }
        
        $sql = "SELECT $columnList FROM $tableName LIMIT $limit";
        $stmt = $pdo->query($sql);
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        throw new Exception('Failed to fetch data: ' . $e->getMessage());
    }
}
?>