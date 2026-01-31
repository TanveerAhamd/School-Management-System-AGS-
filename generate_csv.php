<?php
// FILE: generate_csv.php
require_once 'auth.php';

if (isset($_GET['class_id'])) {
    $filename = "AGS_Bulk_Import_Template.csv";

    // Headers to force download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // ==========================================
    // SECTION 1: REFERENCE DATA (METADATA)
    // ==========================================

    function printRefHeader($file, $title)
    {
        fputcsv($file, ['#', '--------------------------------']);
        fputcsv($file, ['#', "REF LIST: " . strtoupper($title)]);
        fputcsv($file, ['# ID', 'NAME / DETAIL']);
    }

    // 1. SESSIONS
    printRefHeader($output, 'SESSIONS');
    $stmt = $pdo->query("SELECT id, session_name FROM academic_sessions ORDER BY id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$row['id'], $row['session_name']]);
    }

    // 2. CLASSES
    printRefHeader($output, 'CLASSES');
    $stmt = $pdo->query("SELECT id, class_name FROM classes");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$row['id'], $row['class_name']]);
    }

    // 3. SECTIONS (Filtered)
    if (!empty($_GET['class_id'])) {
        printRefHeader($output, 'SECTIONS (For Selected Class)');
        $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
        $stmt->execute([$_GET['class_id']]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [$row['id'], $row['section_name']]);
        }
    }

    // 4. GROUPS
    printRefHeader($output, 'GROUPS');
    $stmt = $pdo->query("SELECT id, group_name FROM subject_groups");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$row['id'], $row['group_name']]);
    }

    // 5. ROUTES
    printRefHeader($output, 'TRANSPORT ROUTES');
    $stmt = $pdo->query("SELECT id, route_name FROM transport_routes");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$row['id'], $row['route_name']]);
    }

    // SEPARATOR LINE
    fputcsv($output, []);
    fputcsv($output, ['#', '==================================================']);
    fputcsv($output, ['#', 'PLEASE ENTER STUDENT DATA BELOW THIS LINE']);
    fputcsv($output, ['#', '==================================================']);
    fputcsv($output, []);

    // ==========================================
    // SECTION 2: ACTUAL DATA HEADERS (33 Columns)
    // ==========================================

    $headers = [
        'Reg No',               // 0
        'Admission Date',       // 1
        'Session ID',           // 2
        'Class ID',             // 3
        'Section ID',           // 4
        'Medium (ENGLISH/URDU)', // 5
        'Group ID',             // 6
        'Student Name',         // 7
        'CNIC/B-Form',          // 8
        'DOB (YYYY-MM-DD)',     // 9
        'Gender (MALE/FEMALE)', // 10
        'Mother Language',      // 11
        'Caste',                // 12
        'Tehsil',               // 13
        'District',             // 14
        'Student Contact',      // 15
        'Student Address',      // 16
        'Guardian Name',        // 17
        'Relation',             // 18
        'Occupation',           // 19
        'Guardian CNIC',        // 20
        'Guardian Contact',     // 21
        'Guardian Address',     // 22
        'Prev School Name',     // 23
        'Last Class',           // 24
        'Passing Year',         // 25
        'Board Name',           // 26
        'Disability (Yes/No)',  // 27
        'Hafiz Quran (Yes/No)', // 28
        'Transport (Yes/No)',   // 29
        'Route ID',             // 30
        'Interests',            // 31
        'Remarks'               // 32
    ];

    fputcsv($output, $headers);

    // ==========================================
    // SECTION 3: SAMPLE ROW
    // Set Sample Date format using ... mm/dd/yy 
    // =IF(ISNUMBER(A2),TEXT(A2,"mm/dd/yyyy"),TEXT(DATE(RIGHT(A2,4),MID(A2,4,2),LEFT(A2,2)),"mm/dd/yyyy"))
    // Change cell name as per your admition date and dob cell individually 
    // ==========================================

    $sess_id = $_GET['session_id'] ?? '';
    $cls_id  = $_GET['class_id'] ?? '';
    $sec_id  = $_GET['section_id'] ?? '';
    $grp_id  = $_GET['subject_group_id'] ?? '';

    $sample_row = [
        '26-AGS-0001',          // Reg No
        date('Y-m-d'),          // Adm Date
        $sess_id,               // Session
        $cls_id,                // Class
        $sec_id,                // Section
        'ENGLISH',              // Medium
        $grp_id,                // Group
        'TANVEER AHMAD',       // Name
        '36101-1234567-1',      // CNIC
        '2010-01-01',           // DOB
        'MALE',               // Gender
        'URDU',                 // Lang
        'JATT',                 // Caste
        'LODHRAN',              // Tehsil
        'LODHRAN',              // District
        '0300-0695646',         // Contact
        'HOUSE #1 ABC ROAD',    // Address
        'GUARDIAN NAME',        // G Name
        'Father',               // Relation
        'Business',             // Occupation
        '36101-9876543-1',      // G CNIC
        '0300-0695646',         // G Contact
        'HOUSE #1 ABC ROAD',    // G Address
        'GOVT SCHOOL',          // Prev School
        '8TH',                  // Last Class
        '2026',                 // Passing Year
        'BISE MULTAN',          // Board
        'No',                   // Disability
        'No',                   // Hafiz
        'No',                   // Transport
        '',                     // Route ID
        'Cricket,Chess',        // Interests
        'Admission Approved'         // Remarks
    ];

    fputcsv($output, $sample_row);

    fclose($output);
    exit;
}
