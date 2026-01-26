<?php
/**
 * 1. SECURITY CHECK
 * auth.php se bnda logged-in hona chahiye
 */
require_once 'auth.php'; 

// Pakistan Timezone taake filename par sahi waqt aye
date_default_timezone_set('Asia/Karachi');

/**
 * 2. DATABASE CONFIGURATION
 * Aapki provide ki hui config ke mutabiq
 */
$host = 'localhost';
$db_name = 'student_management_system'; 
$user = 'root';
$pass = '';

/**
 * 3. DYNAMIC FILENAME WITH DATE & TIME
 * d-M-Y -> 24-Jan-2026
 * h-i-A -> 05-28-AM (A ka matlab AM/PM)
 */
$filename = "SMS_Backup_" . date('d-M-Y_h-i-A') . ".sql";

// Browser Headers taake file download ho jaye
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");

/**
 * 4. DATABASE EXPORT
 */
// XAMPP ki default path (C: Drive)
$mysqldumpPath = "C:/xampp/mysql/bin/mysqldump";

// Command execution
$command = "$mysqldumpPath --opt -h $host -u $user $db_name";

// Seedha output browser ko bhej dena
passthru($command);
exit;
?>