<?php

/**
 * 1. DATABASE CONNECTION & CONFIGURATION
 */
require_once 'auth.php';

$school_prefix = "AGS";
$current_year  = date('y');
$separator     = "-";

// --- TRUE MAX AUTO-INCREMENT LOGIC (Format: 26-AGS-0001) ---
try {
  $pattern_to_search = $current_year . $separator . $school_prefix . $separator . "%";
  $stmt = $pdo->prepare("SELECT reg_no FROM students WHERE reg_no LIKE ?");
  $stmt->execute([$pattern_to_search]);
  $all_regs = $stmt->fetchAll(PDO::FETCH_COLUMN);

  $max_num = 0;
  foreach ($all_regs as $reg) {
    $parts = explode($separator, $reg);
    $num = (int)end($parts);
    if ($num > $max_num) {
      $max_num = $num;
    }
  }
  $next_num = $max_num + 1;
  $new_reg_no = strtoupper($current_year . $separator . $school_prefix . $separator . str_pad($next_num, 4, '0', STR_PAD_LEFT));
} catch (PDOException $e) {
  $new_reg_no = "";
}

/**
 * 2. AJAX HANDLERS
 */
if (isset($_GET['action'])) {
  $action = $_GET['action'];
  header('Content-Type: application/json');

  if ($action == 'fetch_sections') {
    $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
    $stmt->execute([$_GET['class_id'] ?? 0]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
  }

  if ($action == 'fetch_group_subjects' && isset($_GET['group_id'])) {
    $stmt = $pdo->prepare("SELECT s.subject_name FROM subject_group_items sgi 
                               JOIN subjects s ON sgi.subject_id = s.id 
                               WHERE sgi.group_id = ?");
    $stmt->execute([$_GET['group_id']]);
    echo json_encode(['subjects' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    exit;
  }

  if ($action == 'get_all_sessions') {
    $stmt = $pdo->query("SELECT id, session_name, is_active FROM academic_sessions ORDER BY id DESC");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
  }

  if ($action == 'fetch_routes') {
    $stmt = $pdo->query("SELECT id, route_name FROM transport_routes WHERE status = 1");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
  }
}

/**
 * 3. FORM SUBMISSION LOGIC
 */
$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_student'])) {
  try {
    $check = $pdo->prepare("SELECT id FROM students WHERE reg_no = ?");
    $check->execute([$_POST['reg_no']]);
    if ($check->fetch()) {
      throw new Exception("Registration Number already registered.");
    }

    $reg = strtoupper(str_replace([' ', '-'], '_', $_POST['reg_no']));
    $s_name = strtoupper(str_replace(' ', '_', $_POST['student_name']));
    $folder = 'uploads/';
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    function handleMedia($fileKey, $camKey, $docType, $reg, $name, $folder)
    {
      $date = date('Ymd');
      $docType = strtoupper($docType);

      if (!empty($_POST[$camKey]) && strpos($_POST[$camKey], 'base64') !== false) {
        $data = explode(',', $_POST[$camKey])[1];
        $fName = "{$docType}_{$reg}_{$name}_{$date}.png";
        file_put_contents($folder . $fName, base64_decode($data));
        return $folder . $fName;
      } elseif (!empty($_FILES[$fileKey]['name'])) {
        $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
          throw new Exception("Only Images (JPG, PNG) are allowed for $docType.");
        }
        $fName = strtoupper("{$docType}_{$reg}_{$name}_{$date}.{$ext}");
        move_uploaded_file($_FILES[$fileKey]['tmp_name'], $folder . $fName);
        return $folder . $fName;
      }
      return null;
    }

    $photo = handleMedia('student_photo', 'cam_photo_data', 'PHOTO', $reg, $s_name, $folder);
    $cnic_img = handleMedia('cnic_doc', 'cam_cnic_data', 'BFORM', $reg, $s_name, $folder);
    $gf = handleMedia('guardian_cnic_front', 'cam_gf_data', 'GFRONT', $reg, $s_name, $folder);
    $gb = handleMedia('guardian_cnic_back', 'cam_gb_data', 'GBACK', $reg, $s_name, $folder);
    $rc = handleMedia('result_card_doc', 'cam_rc_data', 'RESULTCARD', $reg, $s_name, $folder);

    if (empty($photo) && empty($_POST['cam_photo_data'])) throw new Exception("Student Portrait Photo is required!");

    $sql = "INSERT INTO students (
            reg_no, admission_date, session, class_id, section_id, subject_group_id, 
            student_name, cnic_bform, dob, gender, mother_language, caste, tehsil, district, student_address,
            contact_no, address, guardian_name, relation, guardian_cnic, occupation, guardian_address, guardian_contact, 
            prev_school_name, last_class, passing_year, board_name, disability, 
            hafiz_quran, transport, route_id, interests, remarks, 
            student_photo, cnic_doc, guardian_cnic_front, guardian_cnic_back, result_card_doc, created_at
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $_POST['reg_no'],
      $_POST['admission_date'],
      $_POST['session_id'],
      $_POST['class_id'],
      $_POST['section_id'],
      $_POST['subject_group_id'],
      $_POST['student_name'],
      $_POST['cnic_bform'],
      $_POST['dob'],
      $_POST['gender'],
      $_POST['mother_language'],
      $_POST['caste'],
      $_POST['tehsil'],
      $_POST['district'],
      $_POST['student_address'],
      $_POST['contact_no'],
      $_POST['address'],
      $_POST['guardian_name'],
      $_POST['relation'],
      $_POST['guardian_cnic'],
      $_POST['occupation'],
      $_POST['guardian_address'],
      $_POST['guardian_contact'],
      $_POST['prev_school_name'] ?? '',
      $_POST['last_class'] ?? '',
      $_POST['passing_year'] ?? '',
      $_POST['board_name'] ?? '',
      $_POST['disability'] ?? 'No',
      $_POST['hafiz_quran'] ?? 'No',
      $_POST['transport'],
      ($_POST['transport'] == 'Yes' ? $_POST['route_id'] : null),
      isset($_POST['interests']) ? implode(',', $_POST['interests']) : '',
      $_POST['remarks'] ?? '',
      $photo,
      $cnic_img,
      $gf,
      $gb,
      $rc
    ]);
    $success = true;
  } catch (Exception $e) {
    $error = $e->getMessage();
  }
}

$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$groups_list = $pdo->query("SELECT * FROM subject_groups")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Student Registration | AGS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <style>
    .logo-img {
      height: 40px;
    }

    @media (min-width: 576px) {
      .logo-img {
        height: 60px;
      }
    }

    .badge-subject {
      background: #6777ef;
      color: #fff;
      padding: 2px 8px;
      border-radius: 10px;
      margin: 2px;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .text-danger {
      color: red !important;
      font-weight: bold;
    }

    /* PORTRAIT PASSPORT FRAME */
    .passport-frame {
      height: 160px;
      width: 130px;
      overflow: hidden;
      position: relative;
      border-radius: 4px;
      border: 2px solid #ebedf2;
      background: #000;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* LANDSCAPE DOCUMENT FRAME */
    .doc-landscape-frame {
      height: 110px;
      width: 100%;
      overflow: hidden;
      position: relative;
      border-radius: 4px;
      border: 2px solid #ebedf2;
      background: #000;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Scoped UI Fixes for Images */
    #registrationForm .cam-preview-img {
      height: 100%;
      width: 100%;
      object-fit: cover;
      display: block;
    }

    #registrationForm .cam-video-box {
      height: 100%;
      width: 100%;
      object-fit: contain;
      display: none;
      background: #000;
    }

    .form-section-title {
      font-size: 13px;
      font-weight: 700;
      color: #191d32;
      border-bottom: 1px solid #ebedf2;
      padding-bottom: 5px;
      margin-bottom: 15px;
      text-transform: uppercase;
    }

    .office-use-box {
      background: #fff;
      border: 1px solid #e4e6fc;
      border-radius: 5px;
      padding: 15px;
      height: 100%;
    }
  </style>
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <?php include 'include/navbar.php'; ?>
      <div class="main-sidebar sidebar-style-2"><?php include 'include/asidebar.php'; ?></div>

      <div class="main-content">
        <section class="section">
          <!-- breadcrumb start -->
          <div class="row bg-title">
            <div class="col-12">
              <div class="card mb-3">
                <div class="card-body py-2 b-0">
                  <div class="d-flex flex-wrap align-items-center justify-content-between">

                    <!-- LEFT SIDE -->
                    <div class="mb-2 mb-md-0">
                      <h5 class="page-title mb-0"> </i>Register Student</h5>
                    </div>

                    <!-- RIGHT SIDE -->
                    <div>
                      <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 bg-transparent p-0">
                          <li class="breadcrumb-item">
                            <a href="#"><i class="fas fa-tachometer-alt"></i> Home</a>
                          </li>

                          <li class="breadcrumb-item active" aria-current="page">
                            <i class="fas fa-list"></i> Add
                          </li>
                        </ol>
                      </nav>
                    </div>

                  </div>

                </div>
              </div>
            </div>
          </div>
          <!-- breadcrumb close -->
          <form id="registrationForm" method="POST" enctype="multipart/form-data">
            <div class="card p-2 p-md-4">
              <div class="card-body rounded" style="border: 5px solid #0000004c !important ">

                <div class="row">
                  <div class="col-12 col-lg-10 text-center">
                    <div class="row">
                      <div class="col-md-3 text-center">
                        <div class="d-flex align-items-end justify-content-between justify-content-md-center flex-wrap">

                          <picture class="d-flex justify-content-center">
                            <source media="(min-width: 576px)" srcset="./assets/img/agslogo.png">
                            <img src="./assets/img/agslogo.png" alt="Logo" class="logo-img d-block">
                          </picture>
                          <!-- <img src="./assets/img/agslogo.png" alt="Logo" class="logo-img"> -->
                          <div class="mt-0 mt-md-2 d-flex justify-content-center flex-column">
                            <label class=" d-none d-md-block fw-bold small">Reg #: <span class="text-danger">*</span></label>
                            <input type="text" name="reg_no" value="<?= isset($_POST['reg_no']) ? htmlspecialchars($_POST['reg_no']) : $new_reg_no ?>" class="form-control form-control-sm border-0 border-bottom text-center fw-bold" required>
                            <input type="date" name="admission_date" value="<?= $_POST['admission_date'] ?? date('Y-m-d') ?>" class=" d-none d-lg-block form-control form-control-sm border-0 border-bottom text-center mt-2">
                          </div>
                        </div>
                      </div>

                      <div class="col-md-9">
                        <h5 class="d-md-none text-center text-nowrap my-2">Amina Girls Degree College</h5>
                        <h2 class="d-none d-md-block text-center text-nowrap m-0">Amina Girls Degree College</h2>
                        <div class="text-center">
                          <span class="">Address: Gailywal 21-MPR lodhran</span>
                          <br>
                          <h6
                            class=" mt-2 rounded bg-primary px-3 py-1 d-inline-block text-white text-center mb-0 font-weight-bold">
                            Application Form For Registration </h6>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-3 flex-wrap">
                          <div><label class="fw-bold small">Session <span class="text-danger">*</span></label><select name="session_id" id="session_select" class="form-select form-select-sm" style="width:100px" required></select></div>
                          <div><label class="fw-bold small">Class <span class="text-danger">*</span></label>
                            <select name="class_id" id="sel_class" class="form-select form-select-sm" style="width:100px" required>
                              <option value="">Select</option>
                              <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= (($_POST['class_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= $c['class_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div>
                            <label class="fw-bold small">
                              Medium <span class="text-danger">*</span>
                            </label>

                            <select name="medium" id="sel_medium" class="form-select form-select-sm" style="width:120px" required>
                              <option value="english" <?= (($_POST['medium'] ?? 'english') == 'english') ? 'selected' : '' ?>>
                                English
                              </option>
                              <option value="urdu" <?= (($_POST['medium'] ?? '') == 'urdu') ? 'selected' : '' ?>>
                                Urdu
                              </option>
                            </select>
                          </div>

                          <div><label class="fw-bold small">Section <span class="text-danger">*</span></label><select name="section_id" id="sel_section" class="form-select form-select-sm" style="width:100px" required>
                              <option value="">Section</option>
                            </select></div>
                          <div><label class="fw-bold small">Group <span class="text-danger">*</span></label>
                            <select name="subject_group_id" id="sel_group" class="form-select form-select-sm" style="width:100px" required>
                              <option value="">Select</option>
                              <?php foreach ($groups_list as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= (($_POST['subject_group_id'] ?? '') == $g['id']) ? 'selected' : '' ?>><?= $g['group_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>
                        <div id="group_subjects_view" class="mt-2 p-2 rounded border d-flex justify-content-around" style="background-color: #2727271e; min-height:40px;"></div>
                      </div>
                    </div>
                  </div>

                  <!-- PORTRAIT STUDENT PHOTO -->
                  <div class="col-12 col-lg-2 mt-2 mt-md-0 text-center">
                    <div class="passport-frame">
                      <video id="webcam_main" class="cam-video-box" autoplay playsinline></video>
                      <img id="photoPreview" src="<?= !empty($_POST['cam_photo_data']) ? $_POST['cam_photo_data'] : 'assets/img/userdummypic.png' ?>" class="cam-preview-img">
                      <button type="button" id="cap_btn_main" class="btn btn-sm btn-danger position-absolute" style="bottom: 5px; left: 50%; transform: translateX(-50%); display: none; z-index:10;" onclick="capturePic('main', true)"><i class="fa fa-camera"></i></button>
                    </div>
                    <div class="btn-group w-100 mt-2">
                      <input type="hidden" name="cam_photo_data" id="hid_main" value="<?= htmlspecialchars($_POST['cam_photo_data'] ?? '') ?>">
                      <button type="button" class="btn btn-sm btn-success" onclick="startCam('main')"><i class="fa fa-video"></i></button>
                      <button type="button" class="btn btn-sm btn-info" onclick="document.getElementById('manualFile').click()"><i class="fa fa-upload"></i></button>
                      <input type="file" name="student_photo" id="manualFile" accept="image/*" style="display:none;" onchange="previewManual(this, 'photoPreview', 'hid_main')">
                    </div>
                  </div>
                </div>

                <!-- STUDENT INFO -->
                <h6 class="form-section-title mt-4"><i class='fas fa-user-graduate'></i> Basic Details</h6>
                <div class="row">
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Student Name <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      name="student_name"
                      class="form-control upper-case"
                      style="text-transform: uppercase;"
                      value="<?= htmlspecialchars($_POST['student_name'] ?? '') ?>"
                      required>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">CNIC / B-Form <span class="text-danger">*</span></label>
                    <input type="text" id="mask_cnic" name="cnic_bform" value="<?= htmlspecialchars($_POST['cnic_bform'] ?? '') ?>" class="form-control" required>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Date of Birth: MM/DD/YYYY<span class="text-danger">*</span></label>
                    <input type="date" name="dob" value="<?= $_POST['dob'] ?? '' ?>" class="form-control" required>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-control">
                      <option value="FEMALE" <?= (($_POST['gender'] ?? '') == 'Female' ? 'selected' : '') ?>>Female</option>
                      <option value="MALE" <?= (($_POST['gender'] ?? '') == 'Male' ? 'selected' : '') ?>>Male</option>
                    </select>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Mother Language</label>
                    <input
                      type="text"
                      name="mother_language"
                      value="<?= htmlspecialchars($_POST['mother_language'] ?? '') ?>"
                      class="form-control upper-case"
                      style="text-transform: uppercase;">
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Caste</label>
                    <input
                      type="text"
                      name="caste"
                      value="<?= htmlspecialchars($_POST['caste'] ?? '') ?>"
                      class="form-control upper-case"
                      style="text-transform: uppercase;">
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Tehsil </label>
                    <select name="tehsil" class="form-control">
                      <?php $tehsils = ['Lodhran', 'Kahror Pakka', 'Dunyapur'];
                      foreach ($tehsils as $t) echo "<option value='$t' " . (($_POST['tehsil'] ?? '') == $t ? 'selected' : '') . ">$t</option>"; ?>
                    </select>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">District </label>
                    <select name="district" class="form-control">
                      <?php $districts = ['Lodhran'];
                      foreach ($districts as $d) echo "<option value='$d' " . (($_POST['district'] ?? '') == $d ? 'selected' : '') . ">$d</option>"; ?>
                    </select>
                  </div>

                  <div class="col-md-9 mt-2">
                    <label class="small fw-bold">Student Address</label>
                    <input
                      type="text"
                      id="student_address"
                      name="student_address"
                      value="<?= htmlspecialchars($_POST['student_address'] ?? '') ?>"
                      class="form-control upper-case"
                      style="text-transform: uppercase;">
                  </div>


                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Contact #</label>
                    <input type="text" id="mask_contact" name="contact_no" value="<?= htmlspecialchars($_POST['contact_no'] ?? '') ?>" class="form-control">
                  </div>

                </div>
                <h6 class="form-section-title mt-4"><i class='fas fa-users'></i> Guardian Information</h6>
                <div class="row">
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Guardian Name <span class="text-danger">*</span></label>
                    <input type="text" name="guardian_name" value="<?= htmlspecialchars($_POST['guardian_name'] ?? '') ?>" class="form-control upper-case" style="text-transform: uppercase;" required>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Relation <span class="text-danger">*</span></label>
                    <select name="relation" class="form-control">
                      <?php $rels = ['Father', 'Mother', 'Uncle', 'Brother'];
                      foreach ($rels as $r) echo "<option value='$r' " . (($_POST['relation'] ?? '') == $r ? 'selected' : '') . ">$r</option>"; ?>
                    </select>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Guardian CNIC <span class="text-danger">*</span></label>
                    <input type="text" id="mask_g_cnic" name="guardian_cnic" value="<?= htmlspecialchars($_POST['guardian_cnic'] ?? '') ?>" class="form-control" required>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Occupation</label>
                    <input type="text" name="occupation" value="<?= htmlspecialchars($_POST['occupation'] ?? '') ?>" class="form-control upper-case" style="text-transform: uppercase;">
                  </div>
                  <div class="col-md-8 mt-2">
                    <div class="  d-flex justify-content-between align-items-center">
                      <label class="small fw-bold mb-0">Guardian Address</label>

                      <div class=" ms-3 form-check d-flex align-item-center">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="same_address">
                        <label class="form-check-label small" for="same_address">
                          Same as upper student address
                        </label>
                      </div>
                    </div>

                    <input
                      type="text"
                      id="guardian_address"
                      name="guardian_address"
                      value="<?= htmlspecialchars($_POST['guardian_address'] ?? '') ?>"
                      class="form-control upper-case mt-1"
                      style="text-transform: uppercase;">
                  </div>

                  <div class="col-md-4 mt-2">
                    <label class="small fw-bold">Contact# <span class="text-danger">*</span></label>
                    <input type="text" id="mask_g_contact" name="guardian_contact" value="<?= htmlspecialchars($_POST['guardian_contact'] ?? '') ?>" class="form-control" required>
                  </div>
                </div>

                <h6 class="form-section-title mt-4"><i class='fas fa-university'></i> Previous School Record</h6>
                <div class="row rounded p-2" style="background: #fdfdfd; border:1px solid #ebedf2;">
                  <div class="col-md-5 mt-2">
                    <label class="small fw-bold">Last School Attended</label>
                    <input type="text" name="prev_school_name" value="<?= htmlspecialchars($_POST['prev_school_name'] ?? '') ?>" class="form-control upper-case" style="text-transform: uppercase;">
                  </div>
                  <div class="col-md-2 mt-2">
                    <label class="small fw-bold">Last Class</label>
                    <input type="text" name="last_class" value="<?= htmlspecialchars($_POST['last_class'] ?? '') ?>" class="form-control upper-case" style="text-transform: uppercase;">
                  </div>
                  <div class="col-md-2 mt-2">
                    <label class="small fw-bold">Year of Passing</label>
                    <input type="text" name="passing_year" value="<?= htmlspecialchars($_POST['passing_year'] ?? '') ?>" class="form-control">
                  </div>
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Board Name</label>
                    <input type="text" name="board_name" value="<?= htmlspecialchars($_POST['board_name'] ?? '') ?>" class="form-control upper-case" style="text-transform: uppercase;">
                  </div>
                  <div class="col-md-12 mt-3">
                    <div class="form-check d-flex align-items-center">
                      <input class="form-check-input" type="checkbox" id="declareCheck" style="width: 18px; height: 18px;" required>
                      <label class="form-check-label fw-bold small ml-2" for="declareCheck">I confirm that the details provided are correct.</label>
                    </div>
                  </div>
                </div>
                <!-- DOCUMENTS SECTION -->
                <h6 class="form-section-title mt-4"><i class='fas fa-file-invoice'></i> Documents Upload (Portrait/Landscape)</h6>
                <div class="row">
                  <?php
                  $doc_fields = [
                    ['id' => 'cnic', 'label' => 'B-Form / Student CNIC', 'hid' => 'cam_cnic_data', 'file' => 'cnic_doc'],
                    ['id' => 'gf', 'label' => 'Guardian CNIC Front', 'hid' => 'cam_gf_data', 'file' => 'guardian_cnic_front'],
                    ['id' => 'gb', 'label' => 'Guardian CNIC Back', 'hid' => 'cam_gb_data', 'file' => 'guardian_cnic_back'],
                    ['id' => 'rc', 'label' => 'Last Result Card', 'hid' => 'cam_rc_data', 'file' => 'result_card_doc'],
                  ];
                  foreach ($doc_fields as $df):
                  ?>
                    <div class="col-md-3 mt-2 text-center">
                      <label class="fw-bold x-small" style="font-size:10px"><?= $df['label'] ?></label>
                      <div class="doc-landscape-frame">
                        <video id="webcam_<?= $df['id'] ?>" class="cam-video-box" autoplay playsinline></video>
                        <img id="prev_<?= $df['id'] ?>" src="<?= !empty($_POST[$df['hid']]) ? $_POST[$df['hid']] : 'assets/img/elementor.png' ?>" class="cam-preview-img">
                        <button type="button" id="cap_btn_<?= $df['id'] ?>" class="btn btn-xs btn-danger position-absolute" style="top:5px; right:5px; display:none; z-index:10;" onclick="capturePic('<?= $df['id'] ?>', false)"><i class="fas fa-check"></i></button>
                      </div>
                      <div class="input-group input-group-sm mt-1">
                        <input type="hidden" name="<?= $df['hid'] ?>" id="hid_<?= $df['id'] ?>" value="<?= htmlspecialchars($_POST[$df['hid']] ?? '') ?>">
                        <input type="file" name="<?= $df['file'] ?>" class="form-control" accept="image/*" onchange="previewManual(this, 'prev_<?= $df['id'] ?>', 'hid_<?= $df['id'] ?>')">
                        <button class="btn btn-success" type="button" onclick="startCam('<?= $df['id'] ?>')"><i class="fas fa-camera"></i></button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- OFFICE USE & INTERESTS -->
                <h6 class="form-section-title mt-4"><i class='fas fa-briefcase'></i> Official Use & Sports</h6>
                <div class="row">
                  <div class="col-md-4">
                    <div class="office-use-box shadow-sm">
                      <div class="d-flex justify-content-between mb-2 border-bottom pb-1">
                        <label class="fw-bold small">Disability:</label>
                        <div>
                          <input type="radio" name="disability" value="Yes" <?= (($_POST['disability'] ?? '') == 'Yes' ? 'checked' : '') ?>> <small>Yes</small>
                          <input type="radio" name="disability" value="No" <?= (($_POST['disability'] ?? 'No') == 'No' ? 'checked' : '') ?>> <small>No</small>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between mb-2 border-bottom pb-1">
                        <label class="fw-bold small">Hafiz-Quran:</label>
                        <div>
                          <input type="radio" name="hafiz_quran" value="Yes" <?= (($_POST['hafiz_quran'] ?? '') == 'Yes' ? 'checked' : '') ?>> <small>Yes</small>
                          <input type="radio" name="hafiz_quran" value="No" <?= (($_POST['hafiz_quran'] ?? 'No') == 'No' ? 'checked' : '') ?>> <small>No</small>
                        </div>
                      </div>
                      <div class="mb-2 border-bottom pb-2">
                        <div class="d-flex justify-content-between">
                          <label class="fw-bold small">Transport:</label>
                          <div>
                            <input type="radio" name="transport" value="Yes" class="tr_toggle" <?= (($_POST['transport'] ?? '') == 'Yes' ? 'checked' : '') ?>> <small>Yes</small>
                            <input type="radio" name="transport" value="No" class="tr_toggle" <?= (($_POST['transport'] ?? 'No') == 'No' ? 'checked' : '') ?>> <small>No</small>
                          </div>
                        </div>
                        <select name="route_id" id="sel_route" class="form-control form-control-sm mt-1" <?= (($_POST['transport'] ?? '') != 'Yes' ? 'disabled' : '') ?>></select>
                      </div>
                      <label class="fw-bold small">Interests / Sports:</label>
                      <div class="d-flex flex-wrap gap-1">
                        <?php $ints = ['Cricket', 'Volleyball', 'Chess', 'Taekwondo', 'Scrabble', 'Skating'];
                        $posted_ints = $_POST['interests'] ?? [];
                        if (!is_array($posted_ints)) $posted_ints = explode(',', $posted_ints);
                        foreach ($ints as $i): ?>
                          <div class="custom-control custom-checkbox mr-2">
                            <input type="checkbox" name="interests[]" class="custom-control-input" id="int_<?= $i ?>" value="<?= $i ?>" <?= in_array($i, $posted_ints) ? 'checked' : '' ?>>
                            <label class="custom-control-label small" for="int_<?= $i ?>"><?= $i ?></label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <div class="office-use-box shadow-sm d-flex flex-column justify-content-between">
                      <div><label class="fw-bold small">Official Remarks:</label><textarea name="remarks" class="form-control" rows="5"><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea></div>
                      <div class="text-right mt-3">
                        <div style="border-top: 1.5px solid #333; width: 180px; display: inline-block;">
                          <p class="text-center fw-bold mb-0 small">Auth. Signature</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="text-center mt-4"><button class="btn btn-primary btn-lg px-5 shadow" type="submit" name="save_student">SUBMIT DATA</button></div>
              </div>
            </div>
          </form>
        </section>
      </div>
      <?php include 'include/footer.php'; ?>
    </div>
  </div>

  <canvas id="hidden_canvas" style="display:none;"></canvas>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/js/jquery.inputmask.min.js"></script>
  <script src="./assets/js/sweetalert2.js"></script>

  <script>
    $(window).on('load', function() {
      $('.loader').fadeOut('slow');
    });

    let activeStream = null;

    async function startCam(id) {
      if (activeStream) stopActiveStream();
      $('#registrationForm .cam-video-box').hide();
      $('#registrationForm .cam-preview-img').show();
      $('#registrationForm [id^="cap_btn_"]').hide();

      const video = document.getElementById('webcam_' + id);
      const prevImg = document.getElementById(id === 'main' ? 'photoPreview' : 'prev_' + id);
      const capBtn = document.getElementById('cap_btn_' + id);

      try {
        activeStream = await navigator.mediaDevices.getUserMedia({
          video: {
            width: {
              ideal: 1280
            },
            height: {
              ideal: 720
            }
          }
        });
        video.srcObject = activeStream;
        prevImg.style.display = 'none';
        video.style.display = 'block';
        capBtn.style.display = 'block';
      } catch (err) {
        Swal.fire('Error', 'Camera access denied!', 'error');
      }
    }

    /**
     * CAPTURE WITH PORTRAIT AUTO-CROP FOR STUDENT PIC
     */
    function capturePic(id, isPortrait) {
      const video = document.getElementById('webcam_' + id);
      const canvas = document.getElementById('hidden_canvas');
      const prevImg = document.getElementById(id === 'main' ? 'photoPreview' : 'prev_' + id);
      const hidInput = document.getElementById('hid_' + id);

      const vW = video.videoWidth;
      const vH = video.videoHeight;

      if (isPortrait) {
        // Passport Ratio (3:4 or 4:5)
        const targetRatio = 0.8;
        let cropWidth = vH * targetRatio;
        let startX = (vW - cropWidth) / 2;
        canvas.width = cropWidth;
        canvas.height = vH;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, startX, 0, cropWidth, vH, 0, 0, cropWidth, vH);
      } else {
        canvas.width = vW;
        canvas.height = vH;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, vW, vH);
      }

      const data = canvas.toDataURL('image/png');
      prevImg.src = data;
      hidInput.value = data;
      stopActiveStream();
      video.style.display = 'none';
      prevImg.style.display = 'block';
      document.getElementById('cap_btn_' + id).style.display = 'none';
    }

    function stopActiveStream() {
      if (activeStream) {
        activeStream.getTracks().forEach(t => t.stop());
        activeStream = null;
      }
    }

    function previewManual(input, imgId, hidId) {
      if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = e => {
          document.getElementById(imgId).src = e.target.result;
          document.getElementById(hidId).value = "";
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    $(document).ready(function() {
      $('#mask_cnic, #mask_g_cnic').inputmask("99999-9999999-9");
      $('#mask_contact, #mask_g_contact').inputmask("0399-9999999");

      $.getJSON('?action=get_all_sessions', function(res) {
        let h = '<option value="">Select Session</option>';
        res.data.forEach(s => {
          let sel = (s.id == "<?= $_POST['session_id'] ?? '' ?>" || (s.is_active == 1 && "<?= isset($_POST['session_id']) ? '0' : '1' ?>" == "1")) ? 'selected' : '';
          h += `<option value="${s.id}" ${sel}>${s.session_name}</option>`;
        });
        $('#session_select').html(h);
      });

      $('#sel_class').change(function() {
        $.getJSON('?action=fetch_sections&class_id=' + $(this).val(), function(data) {
          let h = '<option value="">Section</option>';
          data.forEach(d => {
            let sel = (d.id == "<?= $_POST['section_id'] ?? '' ?>") ? 'selected' : '';
            h += `<option value="${d.id}" ${sel}>${d.section_name}</option>`;
          });
          $('#sel_section').html(h);
        });
      }).trigger('change');

      $('#sel_group').change(function() {
        $.getJSON('?action=fetch_group_subjects&group_id=' + $(this).val(), function(data) {
          let h = '<ul class="d-flex justify-content-around list-unstyled w-100 m-0">';
          if (data.subjects.length > 0) {
            data.subjects.forEach(s => h += `<li class="small fw-bold text-uppercase"><b>${s}</b></li>`);
          } else {
            h += '<li class="small fw-bold text-muted">No subjects</li>';
          }
          h += '</ul>';
          $('#group_subjects_view').html(h);
        });
      }).trigger('change');

      $('.tr_toggle').change(function() {
        if ($(this).val() == 'Yes') {
          $('#sel_route').prop('disabled', false);
          $.getJSON('?action=fetch_routes', function(data) {
            let h = '<option value="">Select Route</option>';
            data.forEach(r => {
              let sel = (r.id == "<?= $_POST['route_id'] ?? '' ?>") ? 'selected' : '';
              h += `<option value="${r.id}" ${sel}>${r.route_name}</option>`;
            });
            $('#sel_route').html(h);
          });
        } else {
          $('#sel_route').prop('disabled', true).val('');
        }
      });
      if ($('.tr_toggle:checked').val() == 'Yes') $('.tr_toggle').trigger('change');

      <?php if ($success): ?>
        Swal.fire('Saved!', 'Student Registered Successfully.', 'success').then(() => {
          window.location.href = 'student-registration-form.php';
        });
      <?php elseif ($error): ?>
        Swal.fire('Error!', '<?= addslashes($error) ?>', 'error');
      <?php endif; ?>
    });
  </script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
  <script>
    const checkbox = document.getElementById('same_address');
    const student = document.getElementById('student_address');
    const guardian = document.getElementById('guardian_address');

    checkbox.addEventListener('change', function() {
      if (this.checked) {
        guardian.value = student.value;
        guardian.readOnly = true;
      } else {
        guardian.readOnly = false;
        guardian.value = '';
      }
    });

    // agar student address baad me update ho
    student.addEventListener('input', function() {
      if (checkbox.checked) {
        guardian.value = student.value;
      }
    });
  </script>

</body>

</html>