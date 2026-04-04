<?php
// includes/session.php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isFaculty() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'faculty';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'student';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

function redirectIfNotAdmin() {
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        header("Location: " . BASE_URL . "dashboard.php");
        exit();
    }
}

function redirectIfNotFaculty() {
    redirectIfNotLoggedIn();
    if (!isFaculty()) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

function redirectIfNotStudent() {
    redirectIfNotLoggedIn();
    if (!isStudent()) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

function getUserData($user_id) {
    global $db;
    $query = "SELECT * FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>