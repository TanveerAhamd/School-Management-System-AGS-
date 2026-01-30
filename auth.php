<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();

require_once 'db.php';

// --- AUDIT LOG FUNCTION ---
function create_audit_log($pdo, $admin_id, $action_type, $details = "")
{
    $page_url = $_SERVER['REQUEST_URI'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $sql = "INSERT INTO audit_logs (admin_id, action_type, page_url, action_details, ip_address) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_id, $action_type, $page_url, $details, $ip]);
}

// --- SESSION TIMEOUT LOGIC (5 Minutes) ---
$timeout_duration = 3000; // 5 minutes = 300 seconds

if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    if ($elapsed_time > $timeout_duration) {
        // Session expire ho gaya
        session_unset();
        session_destroy();
        session_start(); // Naya session sirf message dikhane ke liye
        $_SESSION['status'] = "Aapka session khatam ho gaya hai. Kripya dobara login karein.";
        $_SESSION['status_title'] = "Session Expired!";
        $_SESSION['status_type'] = "warning";
        header("Location: index.php");
        exit();
    }
}
// Har baar page load hone par time update karein
$_SESSION['last_activity'] = time();

// --- AUTHORIZATION CHECK ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// --- ADMIN DATA FETCH ---
$stmt = $pdo->prepare("SELECT id, full_name, email FROM admins WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

if ($admin) {
    $current_admin_id = $admin['id'];
    $current_admin_name = $admin['full_name'];

    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
        create_audit_log($pdo, $current_admin_id, 'LOGIN', 'Admin login successful.');
    }
} else {
    session_destroy();
    header("Location: index.php");
    exit();
}
