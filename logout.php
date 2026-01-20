<?php
session_start();
require_once 'db.php'; // Audit log ke liye database zaroori hai

// 1. Logout ko Audit Log mein record karna
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $page = $_SERVER['REQUEST_URI'];

    $sql = "INSERT INTO audit_logs (admin_id, action_type, page_url, action_details, ip_address) 
            VALUES (?, 'LOGOUT', ?, 'Admin ne successfully logout kiya.', ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_id, $page, $ip]);
}

// 2. Tamam Session variables ko khatam karna
$_SESSION = array();

// 3. Session cookie ko browser se delete karna
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Session ko server se destroy karna
session_destroy();

// 5. Redirect karne se pehle aik naya session start karein taake Login page pe message dikha saken
session_start();
$_SESSION['status'] = "Aap kamyabi se logout ho chuke hain.";
$_SESSION['status_title'] = "Logout!";
$_SESSION['status_type'] = "success";

header("Location: index.php");
exit();
