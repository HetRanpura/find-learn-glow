<?php
/**
 * db.php — Database Connection
 * HomeTutor Finder & Scheduling System
 * Database: tutor_db | User: root | Pass: (none)
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tutor_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'error' => true,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');

// Session helper: start once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize user input
 */
function sanitize($conn, $data) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($data))));
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Flash message helper
 */
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/**
 * Auth guard — call at top of protected pages
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}