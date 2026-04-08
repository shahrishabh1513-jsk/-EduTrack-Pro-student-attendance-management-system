<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if (!isLoggedIn() || !hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

// Create settings table if not exists
$create_table = "CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_table);

switch($action) {
    case 'get':
        $key = isset($_GET['key']) ? sanitize($_GET['key']) : '';
        
        if ($key) {
            $query = "SELECT * FROM system_settings WHERE setting_key = '$key'";
            $result = mysqli_query($conn, $query);
            $setting = mysqli_fetch_assoc($result);
            
            if ($setting) {
                $value = $setting['setting_value'];
                if ($setting['setting_type'] === 'boolean') {
                    $value = $value === 'true' || $value === '1';
                } elseif ($setting['setting_type'] === 'json') {
                    $value = json_decode($value, true);
                }
                echo json_encode(['success' => true, 'data' => ['key' => $key, 'value' => $value]]);
            } else {
                echo json_encode(['success' => true, 'data' => null]);
            }
        } else {
            $query = "SELECT * FROM system_settings";
            $result = mysqli_query($conn, $query);
            $settings = [];
            while($row = mysqli_fetch_assoc($result)) {
                $value = $row['setting_value'];
                if ($row['setting_type'] === 'boolean') {
                    $value = $value === 'true' || $value === '1';
                } elseif ($row['setting_type'] === 'json') {
                    $value = json_decode($value, true);
                }
                $settings[$row['setting_key']] = $value;
            }
            echo json_encode(['success' => true, 'data' => $settings]);
        }
        break;
        
    case 'set':
        $data = json_decode(file_get_contents('php://input'), true);
        $key = sanitize($data['key']);
        $value = $data['value'];
        $type = sanitize($data['type'] ?? 'text');
        
        // Convert value based on type
        if ($type === 'boolean') {
            $db_value = $value ? 'true' : 'false';
        } elseif ($type === 'json') {
            $db_value = json_encode($value);
        } else {
            $db_value = sanitize($value);
        }
        
        $check_query = "SELECT id FROM system_settings WHERE setting_key = '$key'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $query = "UPDATE system_settings SET setting_value = '$db_value', setting_type = '$type' WHERE setting_key = '$key'";
        } else {
            $query = "INSERT INTO system_settings (setting_key, setting_value, setting_type) VALUES ('$key', '$db_value', '$type')";
        }
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Setting saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving setting']);
        }
        break;
        
    case 'get_all':
        $query = "SELECT * FROM system_settings";
        $result = mysqli_query($conn, $query);
        $settings = [];
        while($row = mysqli_fetch_assoc($result)) {
            $value = $row['setting_value'];
            if ($row['setting_type'] === 'boolean') {
                $value = $value === 'true' || $value === '1';
            } elseif ($row['setting_type'] === 'json') {
                $value = json_decode($value, true);
            }
            $settings[$row['setting_key']] = $value;
        }
        
        // Default settings if not exist
        $defaults = [
            'system_name' => 'EduTrack Pro',
            'system_url' => 'http://localhost/edutrack-pro',
            'admin_email' => 'admin@edutrack.com',
            'timezone' => 'Asia/Kolkata',
            'date_format' => 'DD/MM/YYYY',
            'min_attendance' => 75,
            'exam_fee' => 500,
            'practical_fee' => 1000,
            'academic_year' => date('Y') . '-' . (date('Y') + 1),
            'current_semester' => 3,
            'email_notifications' => true,
            'two_factor_auth' => false
        ];
        
        foreach ($defaults as $key => $default_value) {
            if (!isset($settings[$key])) {
                $type = is_bool($default_value) ? 'boolean' : (is_array($default_value) ? 'json' : 'text');
                $db_value = is_bool($default_value) ? ($default_value ? 'true' : 'false') : (is_array($default_value) ? json_encode($default_value) : $default_value);
                $insert = "INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type) VALUES ('$key', '$db_value', '$type')";
                mysqli_query($conn, $insert);
                $settings[$key] = $default_value;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $settings]);
        break;
        
    case 'backup':
        $type = isset($_GET['type']) ? sanitize($_GET['type']) : 'full';
        
        $tables = [];
        if ($type === 'full') {
            $tables = ['users', 'students', 'faculty', 'courses', 'attendance', 'leave_applications', 'grievances', 'notices', 'exam_forms', 'feedback', 'exam_results', 'system_settings'];
        } elseif ($type === 'students') {
            $tables = ['users', 'students', 'attendance', 'leave_applications', 'grievances', 'feedback'];
        } elseif ($type === 'reports') {
            $tables = ['exam_forms', 'exam_results'];
        }
        
        $backup_sql = "-- EduTrack Pro Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n-- Type: $type\n\n";
        
        foreach ($tables as $table) {
            $result = mysqli_query($conn, "SELECT * FROM $table");
            if (mysqli_num_rows($result) > 0) {
                $backup_sql .= "DROP TABLE IF EXISTS `$table`;\n";
                $create = mysqli_query($conn, "SHOW CREATE TABLE $table");
                $row = mysqli_fetch_assoc($create);
                $backup_sql .= $row['Create Table'] . ";\n\n";
                
                while ($row_data = mysqli_fetch_assoc($result)) {
                    $columns = array_keys($row_data);
                    $values = array_map(function($value) use ($conn) {
                        return "'" . mysqli_real_escape_string($conn, $value) . "'";
                    }, array_values($row_data));
                    $backup_sql .= "INSERT INTO `$table` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $values) . ");\n";
                }
                $backup_sql .= "\n";
            }
        }
        
        $filename = $type . "_backup_" . date('Y_m_d_H_i_s') . ".sql";
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $backup_sql;
        break;
        
    case 'logs':
        $level = isset($_GET['level']) ? sanitize($_GET['level']) : '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        
        $where = "";
        if ($level) {
            $where = "WHERE level = '$level'";
        }
        
        $query = "SELECT * FROM system_logs $where ORDER BY created_at DESC LIMIT $limit";
        $result = mysqli_query($conn, $query);
        $logs = [];
        while($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $logs]);
        break;
        
    case 'clear_logs':
        $query = "TRUNCATE TABLE system_logs";
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Logs cleared successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error clearing logs']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>