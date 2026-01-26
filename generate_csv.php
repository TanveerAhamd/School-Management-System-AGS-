<?php
require_once 'auth.php';

if (isset($_GET['class_id'])) {
    $filename = "AGS_Bulk_Import_Template.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // These 33 Columns match the array indices (0-32) in add-bulk-student.php
    fputcsv($output, [
        'reg_no', 'admission_date', 'session_id', 'class_id', 'section_id', 'subject_group_id', 
        'student_name', 'cnic_bform', 'dob', 'gender', 'mother_language', 'caste', 'contact_no', 
        'address', 'guardian_name', 'relation', 'occupation', 'guardian_contact', 
        'prev_school_name', 'last_class', 'passing_year', 'board_name', 'disability', 
        'hafiz_quran', 'transport', 'route_id', 'interests', 'remarks', 'student_photo', 
        'cnic_doc', 'guardian_cnic_front', 'guardian_cnic_back', 'result_card_doc'
    ]);

    // Sample Row: Interests format 'Cricket,Chess' enables checkboxes on Edit Page
    fputcsv($output, [
        '26-AGS-0001', date('Y-m-d'), $_GET['session_id'], $_GET['class_id'], $_GET['section_id'], $_GET['subject_group_id'],
        'SAMPLE STUDENT', '31101-1111111-1', '2010-01-01', 'Male', 'Urdu', 'Caste', '0300-1111111',
        'Address Here', 'Guardian Name', 'Father', 'Business', '0300-2222222',
        'Prev School', '8th', '2024', 'BISE MULTAN', 'No', 
        'No', 'No', '0', 'Cricket,Volleyball', 'Good', '', '', '', '', ''
    ]);

    fclose($output);
    exit;
}