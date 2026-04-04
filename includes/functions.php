<?php
// includes/functions.php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateUniqueID($prefix) {
    return $prefix . uniqid() . rand(100, 999);
}

function uploadFile($file, $target_dir) {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    return false;
}

function getAttendancePercentage($student_id, $subject_id = null) {
    global $db;
    
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
              FROM attendance 
              WHERE student_id = :student_id";
    
    if ($subject_id) {
        $query .= " AND subject_id = :subject_id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    if ($subject_id) {
        $stmt->bindParam(':subject_id', $subject_id);
    }
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        return round(($result['present'] / $result['total']) * 100, 2);
    }
    return 0;
}

function logActivity($user_id, $action, $details = null) {
    global $db;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $query = "INSERT INTO system_logs (user_id, action, details, ip_address, user_agent) 
              VALUES (:user_id, :action, :details, :ip_address, :user_agent)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':details', $details);
    $stmt->bindParam(':ip_address', $ip_address);
    $stmt->bindParam(':user_agent', $user_agent);
    return $stmt->execute();
}

function getStudentIdByUserId($user_id) {
    global $db;
    $query = "SELECT id FROM students WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : null;
}

function getFacultyIdByUserId($user_id) {
    global $db;
    $query = "SELECT id FROM faculty WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : null;
}
?>