<?php
/**
 * EduTrack Pro - Database Connection
 * Handles database connectivity and configuration
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'edutrack_db');

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Create connection using MySQLi
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Create PDO connection for advanced queries
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

// Function to get connection status
function getConnectionStatus() {
    global $conn;
    return mysqli_ping($conn);
}

// Function to close connection
function closeConnection() {
    global $conn;
    if ($conn) {
        mysqli_close($conn);
    }
}

// Function to escape string
function escapeString($string) {
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}

// Function to get last inserted ID
function getLastInsertId() {
    global $conn;
    return mysqli_insert_id($conn);
}

// Function to get affected rows
function getAffectedRows() {
    global $conn;
    return mysqli_affected_rows($conn);
}

// Function to begin transaction
function beginTransaction() {
    global $conn;
    mysqli_begin_transaction($conn);
}

// Function to commit transaction
function commitTransaction() {
    global $conn;
    mysqli_commit($conn);
}

// Function to rollback transaction
function rollbackTransaction() {
    global $conn;
    mysqli_rollback($conn);
}

// Function to execute query with error handling
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        throw $e;
    }
}

// Function to fetch single row
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

// Function to fetch all rows
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

// Function to insert data
function insert($table, $data) {
    $fields = array_keys($data);
    $placeholders = array_fill(0, count($fields), '?');
    
    $sql = "INSERT INTO $table (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = executeQuery($sql, array_values($data));
    return getLastInsertId();
}

// Function to update data
function update($table, $data, $where, $whereParams = []) {
    $set = [];
    foreach ($data as $key => $value) {
        $set[] = "`$key` = ?";
    }
    
    $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE $where";
    $params = array_merge(array_values($data), $whereParams);
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}

// Function to delete data
function delete($table, $where, $params = []) {
    $sql = "DELETE FROM $table WHERE $where";
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}

// Function to check if table exists
function tableExists($tableName) {
    global $conn;
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$tableName'");
    return mysqli_num_rows($result) > 0;
}

// Function to get table structure
function getTableStructure($tableName) {
    global $conn;
    $result = mysqli_query($conn, "DESCRIBE $tableName");
    $structure = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $structure[] = $row;
    }
    return $structure;
}
?>      