<?php

/**
 * FINAL CORRECTED FILE
 * Features: 
 * 1. PRG Pattern (Fixes SweetAlert on Refresh)
 * 2. Pre-filled Data (via AJAX)
 * 3. Strict Image Handling
 */
require_once 'auth.php';

// --- 1. AJAX HANDLERS ---
if (isset($_GET['action'])) {
  header('Content-Type: application/json');

  if ($_GET['action'] == 'fetch_student_full' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
  }

  if ($_GET['action'] == 'fetch_sections') {
    $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
    $stmt->execute([$_GET['class_id'] ?? 0]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
  }

  if ($_GET['action'] == 'fetch_group_subjects' && isset($_GET['group_id'])) {
    $stmt = $pdo->prepare("SELECT s.subject_name FROM subject_group_items sgi 
                               JOIN subjects s ON sgi.subject_id = s.id WHERE sgi.group_id = ?");
    $stmt->execute([$_GET['group_id']]);
    echo json_encode(['subjects' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    exit;
  }

  if ($_GET['action'] == 'fetch_routes') {
    $stmt = $pdo->query("SELECT id, route_name FROM transport_routes WHERE status = 1");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
  }

  if ($_GET['action'] == 'get_all_sessions') {
    $stmt = $pdo->query("SELECT id, session_name, is_active FROM academic_sessions ORDER BY id DESC");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
  }
}

// --- 2. UPDATE LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_student'])) {
  try {
    $id = $_POST['student_db_id'];
    $reg_clean = strtoupper(str_replace([' ', '-'], '_', $_POST['reg_no']));
    $name_clean = strtoupper(str_replace(' ', '_', $_POST['student_name']));
    $folder = 'uploads/';

    // Function handles replacement: Deletes old file ONLY if new one is uploaded
    function handleMediaUpdate($fileKey, $camKey, $docType, $reg, $name, $folder, $id, $pdo)
    {
      $stmt = $pdo->prepare("SELECT $fileKey FROM students WHERE id = ?");
      $stmt->execute([$id]);
      $oldPath = $stmt->fetchColumn();

      $date_time = date('Ymd_His');
      $docType = strtoupper($docType);
      $newPath = null;

      // 1. Check Webcam Data
      if (!empty($_POST[$camKey]) && strpos($_POST[$camKey], 'base64') !== false) {
        $data = explode(',', $_POST[$camKey])[1];
        $fName = "{$docType}_{$reg}_{$name}_{$date_time}.png";
        file_put_contents($folder . $fName, base64_decode($data));
        $newPath = $folder . $fName;
      }
      // 2. Check File Upload
      elseif (!empty($_FILES[$fileKey]['name'])) {
        $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
        $fName = strtoupper("{$docType}_{$reg}_{$name}_{$date_time}.{$ext}");
        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $folder . $fName)) {
          $newPath = $folder . $fName;
        }
      }

      // 3. Logic: If new file exists, delete old one. Else return old path.
      if ($newPath) {
        if ($oldPath && file_exists($oldPath) && !strpos($oldPath, 'userdummypic') && !strpos($oldPath, 'elementor')) {
          unlink($oldPath); // Purani file delete
        }
        return $newPath;
      }
      return $oldPath; // Purani file wapis
    }

    $photo = handleMediaUpdate('student_photo', 'cam_photo_data', 'PHOTO', $reg_clean, $name_clean, $folder, $id, $pdo);
    $cnic = handleMediaUpdate('cnic_doc', 'cam_cnic_data', 'BFORM', $reg_clean, $name_clean, $folder, $id, $pdo);
    $gf = handleMediaUpdate('guardian_cnic_front', 'cam_gf_data', 'GFRONT', $reg_clean, $name_clean, $folder, $id, $pdo);
    $gb = handleMediaUpdate('guardian_cnic_back', 'cam_gb_data', 'GBACK', $reg_clean, $name_clean, $folder, $id, $pdo);
    $rc = handleMediaUpdate('result_card_doc', 'cam_rc_data', 'RESULTCARD', $reg_clean, $name_clean, $folder, $id, $pdo);

    $sql = "UPDATE students SET 
    reg_no=?, admission_date=?, session=?, class_id=?, section_id=?, 
    medium=?, subject_group_id=?, student_name=?, cnic_bform=?, dob=?, 
    gender=?, mother_language=?, caste=?, tehsil=?, district=?, 
    student_contact=?, student_address=?, guardian_name=?, relation=?, occupation=?, 
    guardian_address=?, guardian_contact=?, guardian_cnic=?, prev_school_name=?, last_class=?, 
    passing_year=?, board_name=?, disability=?, hafiz_quran=?, transport=?, 
    route_id=?, interests=?, remarks=?, student_photo=?, cnic_doc=?, 
    guardian_cnic_front=?, guardian_cnic_back=?, result_card_doc=? 
    WHERE id=?";

    $pdo->prepare($sql)->execute([
      $_POST['reg_no'],
      $_POST['admission_date'],
      $_POST['session_id'],
      $_POST['class_id'],
      $_POST['section_id'],
      $_POST['medium'],
      $_POST['subject_group_id'],
      strtoupper($_POST['student_name']),
      $_POST['cnic_bform'],
      $_POST['dob'],
      $_POST['gender'],
      $_POST['mother_language'],
      $_POST['caste'],
      $_POST['tehsil'],
      $_POST['district'],
      $_POST['student_contact'],
      $_POST['student_address'],
      $_POST['guardian_name'],
      $_POST['relation'],
      $_POST['occupation'],
      $_POST['guardian_address'],
      $_POST['guardian_contact'],
      $_POST['guardian_cnic'],
      $_POST['prev_school_name'] ?? '',
      $_POST['last_class'] ?? '',
      $_POST['passing_year'] ?? '',
      $_POST['board_name'] ?? '',
      $_POST['disability'] ?? 'No',
      $_POST['hafiz_quran'] ?? 'No',
      $_POST['transport'],
      ($_POST['transport'] == 'Yes' ? $_POST['route_id'] : null),
      (isset($_POST['interests']) ? implode(',', $_POST['interests']) : ''),
      $_POST['remarks'] ?? '',
      $photo,
      $cnic,
      $gf,
      $gb,
      $rc,
      $id
    ]);

    // --- FIX FOR SWEET ALERT LOOP ---
    // Redirect to the same page with a success flag in URL
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id . "&status=success");
    exit;
  } catch (Exception $e) {
    // Error stays on page to show message
    $error = $e->getMessage();
  }
}

$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$groups_list = $pdo->query("SELECT * FROM subject_groups")->fetchAll();
?>

<?php
// ڈیٹا بیس سے اسکول کی تمام سیٹنگز فیچ کریں
$school_settings = $pdo->query("SELECT * FROM school_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// ویری ایبلز سیٹ کریں (ڈیفالٹ ویلیوز کے ساتھ)
$sch_name    = !empty($school_settings['school_name']) ? $school_settings['school_name'] : "Amina Girls High School";
$sch_address = !empty($school_settings['address'])     ? $school_settings['address']     : "Adda Sikandri 21/MPR Gailywal, Lodhran";
$sch_contact = !empty($school_settings['contact'])     ? $school_settings['contact']     : "0300-1234567";
$sch_logo    = (!empty($school_settings['logo']) && file_exists('uploads/' . $school_settings['logo']))
  ? 'uploads/' . $school_settings['logo']
  : 'assets/img/agslogo.png';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Edit Student Profile | AGHS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="icon" href="assets/img/favicon.png">

  <style>
    .logo-img {
      height: 40px;
    }

    @media (min-width: 576px) {
      .logo-img {
        height: 60px;
      }
    }

    .badge-subject-item {
      background: #6777ef;
      color: #fff;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
      margin: 2px;
    }

    .text-danger {
      color: red !important;
      font-weight: bold;
    }

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

    .edit-cam-img {
      height: 100%;
      width: 100%;
      object-fit: contain;
      display: block;
    }

    .edit-cam-vid {
      height: 100%;
      width: 100%;
      object-fit: contain;
      display: none;
      background: #000;
    }

    .sync-msg {
      font-size: 12px;
      color: orange;
      display: none;
      margin-left: 10px;
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
          <div class="row bg-title">
            <div class="col-12">
              <div class="card mb-3">
                <div class="card-body py-2 b-0 d-flex justify-content-between align-items-center">
                  <h5 class="page-title mb-0">Edit Student Record</h5>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">
                      <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                      <li class="breadcrumb-item active">Edit</li>
                    </ol>
                  </nav>
                </div>
              </div>
            </div>
          </div>

          <form id="registrationForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="student_db_id" value="<?= $_GET['id'] ?>">

            <div class="card p-2 p-md-4">
              <div class="card-body rounded" style="border: 5px solid #0000004c !important ">

                <div class="row">
                  <div class="col-12 col-lg-10 text-center">
                    <div class="row">
                      <div class="col-md-3 text-left">
                        <!-- 1. Dynamic Logo -->
                        <picture class="d-flex justify-content-center">
                          <source media="(min-width: 576px)" srcset="<?= $sch_logo ?>">
                          <img src="<?= $sch_logo ?>" alt="School Logo" class="logo-img d-block" style="max-height: 90px; width: auto; object-fit: contain;">
                        </picture>
                        <div class="mt-3 text-center">
                          <label class="fw-bold small">Reg #: <span class="text-danger">*</span></label>
                          <input type="text" name="reg_no" id="reg_no" class="form-control form-control-sm text-center fw-bold" required>
                          <input type="date" name="admission_date" id="admission_date" class="form-control form-control-sm text-center mt-2">
                        </div>
                      </div>
                      <div class="col-md-9 text-center">
                        <!-- 2. Dynamic Title (Responsive) -->
                        <h5 class="d-md-none text-center text-nowrap my-2 font-weight-bold"><?= htmlspecialchars($sch_name) ?></h5>
                        <h2 class="d-none d-md-block text-center text-nowrap m-0 font-weight-bold" style="letter-spacing: 2px;">
                          <?= htmlspecialchars($sch_name) ?>
                        </h2>
                        <div class="text-center">
                          <span class="text-center text-muted  py-3">
                            <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($sch_address) ?>
                          </span>
                          <br>
                          <h6
                            class=" mt-2 rounded bg-primary px-3 py-2 my-2 d-inline-block text-white text-center mb-0 font-weight-bold">
                            Edit Student Record </h6>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-3 flex-wrap">


                          <div><label class="small fw-bold">Session <span class="text-danger">*</span></label><select name="session_id" id="session_select" class="form-select form-select-sm" style="width:110px" required></select></div>
                          <div><label class="small fw-bold">Class <span class="text-danger">*</span></label>
                            <select name="class_id" id="sel_class" class="form-select form-select-sm" style="width:110px" required>
                              <option value="">Select</option>
                              <?php foreach ($classes as $c) echo "<option value='{$c['id']}'>{$c['class_name']}</option>"; ?>
                            </select>
                          </div>
                          <div><label class="small fw-bold">Section <span class="text-danger">*</span></label><select name="section_id" id="sel_section" class="form-select form-select-sm" style="width:110px" required>
                              <option value="">Section</option>
                            </select></div>
                          <div>
                            <label class="fw-bold small">Medium <span class="text-danger">*</span></label>
                            <select name="medium" id="medium" class="form-select form-select-sm" style="width:120px" required>
                              <option value="ENGLISH">English</option>
                              <option value="URDU">Urdu</option>
                            </select>
                          </div>
                          <div><label class="small fw-bold">Group <span class="text-danger">*</span></label>
                            <select name="subject_group_id" id="sel_group" class="form-select form-select-sm" style="width:110px" required>
                              <option value="">Select</option>
                              <?php foreach ($groups_list as $g) echo "<option value='{$g['id']}'>{$g['group_name']}</option>"; ?>
                            </select>
                          </div>
                        </div>
                        <div id="group_subjects_view" class="mt-2 p-2 rounded border d-flex justify-content-around" style="background-color: #f4f4f4; min-height:40px;"></div>
                      </div>
                    </div>
                  </div>

                  <div class="col-12 col-lg-2 text-center">
                    <div class="passport-frame">
                      <video id="webcam_main" class="edit-cam-vid" autoplay playsinline></video>
                      <img id="photoPreview" src="assets/img/userdummypic.png" class="edit-cam-img img-fluid">
                      <button type="button" id="cap_btn_main" class="btn btn-sm btn-danger position-absolute" style="bottom: 5px; left: 50%; transform: translateX(-50%); display: none; z-index:10;" onclick="capturePic('main', true)"><i class="fa fa-camera"></i></button>
                    </div>
                    <div class="btn-group w-100 mt-2">
                      <input type="hidden" name="cam_photo_data" id="hid_main">
                      <button type="button" class="btn btn-sm btn-success" onclick="startCam('main')"><i class="fa fa-video"></i></button>
                      <button type="button" class="btn btn-sm btn-info" onclick="document.getElementById('manualFile').click()"><i class="fa fa-upload"></i></button>
                      <input type="file" name="student_photo" id="manualFile" style="display:none;" onchange="previewManual(this, 'photoPreview', 'hid_main')">
                    </div>
                  </div>
                </div>

                <!-- STUDENT INFO SECTION -->
                <h6 class="form-section-title mt-4">Student Details</h6>
                <div class="row">
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Student Name <span class="text-danger">*</span></label>
                    <input type="text" name="student_name" id="student_name" class="form-control" required style="text-transform: uppercase;">
                  </div>
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">CNIC / B-Form <span class="text-danger">*</span></label>
                    <input type="text" id="cnic_bform" name="cnic_bform" class="form-control" required>
                  </div>
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Date of Birth <span class="text-danger">* </span>mm/dd/yy</label>
                    <input type="date" name="dob" id="dob" class="form-control" required>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Gender <span class="text-danger">*</span></label>
                    <select name="gender" id="gender" class="form-control">
                      <option value="FEMALE">FEMALE</option>
                      <option value="MALE">MALE</option>
                    </select>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Language</label>
                    <input type="text" name="mother_language" id="mother_language" class="form-control" style="text-transform: uppercase;">
                  </div>
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Caste</label>
                    <input type="text" name="caste" id="caste" class="form-control" style="text-transform: uppercase;">
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Tehsil</label>
                    <select name="tehsil" id="tehsil" class="form-control">
                      <option value="LODHRAN">LODHRAN</option>
                      <option value="KEHROR PAKKA">KEHROR PAKKA</option>
                      <option value="DUNYAPUR">DUNYAPUR</option>
                    </select>
                  </div>
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">District</label>
                    <select name="district" id="district" class="form-control">
                      <option value="LODHRAN">LODHRAN</option>
                    </select>
                  </div>

                  <div class="col-md-9 mt-2">
                    <label class="small fw-bold">Student Address</label>
                    <input type="text" name="student_address" id="student_address" class="form-control" style="text-transform: uppercase;">
                  </div>
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Contact #</label>
                    <input type="text" id="contact_no" name="student_contact" class="form-control">
                  </div>

                </div>

                <!-- GUARDIAN INFO -->
                <h6 class="form-section-title mt-4">Guardian Information</h6>
                <div class="row">
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Guardian Name <span class="text-danger">*</span></label>
                    <input type="text" name="guardian_name" id="guardian_name" class="form-control" required style="text-transform: uppercase;">
                  </div>
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Relation *</label>
                    <select name="relation" id="relation" class="form-control">
                      <option value="Father">Father</option>
                      <option value="Mother">Mother</option>
                      <option value="Uncle">Uncle</option>
                      <option value="Brother">Brother</option>
                    </select>
                  </div>
                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Guardian CNIC <span class="text-danger">*</span></label>
                    <input type="text" id="guardian_cnic" name="guardian_cnic" class="form-control" required>
                  </div>

                  <div class="col-md-3 mt-2">
                    <label class="small fw-bold">Guardian Contact *</label>
                    <input type="text" id="guardian_contact" name="guardian_contact" class="form-control" required>
                  </div>
                  <div class="col-md-4 mt-2">
                    <label class="small fw-bold">Occupation</label>
                    <input type="text" name="occupation" id="occupation" class="form-control" style="text-transform: uppercase;">
                  </div>
                  <div class="col-md-8 mt-2">
                    <div class="d-flex justify-content-between align-items-center">
                      <label class="small fw-bold mb-0">Guardian Address</label>
                      <div class="ms-3 form-check d-flex align-item-center">
                        <input class="form-check-input" type="checkbox" id="same_address">
                        <label class="form-check-label small" for="same_address">Same as student address</label>
                      </div>
                    </div>
                    <input type="text" id="guardian_address" name="guardian_address" class="form-control mt-1" style="text-transform: uppercase;">
                  </div>
                </div>

                <!-- PREVIOUS SCHOOL -->
                <h6 class="form-section-title mt-4">Previous School Record</h6>
                <div class="row rounded p-2" style="background: #fdfdfd; border:1px solid #ebedf2;">
                  <div class="col-md-5 mt-2"><label class="small fw-bold">School Name</label><input type="text" name="prev_school_name" id="prev_school_name" class="form-control" style="text-transform: uppercase;"></div>
                  <div class="col-md-2 mt-2"><label class="small fw-bold">Last Class</label><input type="text" name="last_class" id="last_class" class="form-control" style="text-transform: uppercase;"></div>
                  <div class="col-md-2 mt-2"><label class="small fw-bold">Passing Year</label><input type="text" name="passing_year" id="passing_year" class="form-control"></div>
                  <div class="col-md-3 mt-2"><label class="small fw-bold">Board Name</label><input type="text" name="board_name" id="board_name" class="form-control" style="text-transform: uppercase;"></div>
                </div>

                <!-- DOCUMENTS SECTION -->
                <div class="row mt-4">
                  <?php
                  $doc_boxes = [['id' => 'cnic', 'label' => 'B-Form Img', 'hid' => 'cam_cnic_data', 'file' => 'cnic_doc'], ['id' => 'gf', 'label' => 'Guardian Front', 'hid' => 'cam_gf_data', 'file' => 'guardian_cnic_front'], ['id' => 'gb', 'label' => 'Guardian Back', 'hid' => 'cam_gb_data', 'file' => 'guardian_cnic_back'], ['id' => 'rc', 'label' => 'Result Card', 'hid' => 'cam_rc_data', 'file' => 'result_card_doc']];
                  foreach ($doc_boxes as $dbx):
                  ?>
                    <div class="col-md-3 mt-2 text-center">
                      <label class="fw-bold" style="font-size:10px"><?= $dbx['label'] ?></label>
                      <div class="doc-landscape-frame">
                        <video id="webcam_<?= $dbx['id'] ?>" class="edit-cam-vid" autoplay playsinline></video>
                        <img id="prev_<?= $dbx['id'] ?>" src="assets/img/elementor.png" class="edit-cam-img img-fluid">
                        <button type="button" id="cap_btn_<?= $dbx['id'] ?>" class="btn btn-xs btn-danger position-absolute" style="top:5px; right:5px; display:none;" onclick="capturePic('<?= $dbx['id'] ?>', false)"><i class="fas fa-check"></i></button>
                      </div>
                      <div class="input-group input-group-sm mt-1">
                        <input type="hidden" name="<?= $dbx['hid'] ?>" id="hid_<?= $dbx['id'] ?>">
                        <input type="file" name="<?= $dbx['file'] ?>" class="form-control" accept="image/*" onchange="previewManual(this, 'prev_<?= $dbx['id'] ?>', 'hid_<?= $dbx['id'] ?>')">
                        <button type="button" class="btn btn-success btn-xs" onclick="startCam('<?= $dbx['id'] ?>')"><i class="fas fa-camera"></i></button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- OFFICE USE & INTERESTS -->
                <h6 class="form-section-title mt-4"><i class='fas fa-briefcase'></i> Official Use & Sports</h6>
                <div class="row">
                  <div class="col-md-4">
                    <div class="office-use-box shadow-sm">
                      <div class="d-flex justify-content-between mb-2 border-bottom pb-1"><label class="fw-bold small">Disability:</label>
                        <div id="dis_box">
                          <input type="radio" name="disability" value="Yes"> <small>Yes</small>
                          <input type="radio" name="disability" value="No"> <small>No</small>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between mb-2 border-bottom pb-1"><label class="fw-bold small">Hafiz-Quran:</label>
                        <div id="hafiz_box">
                          <input type="radio" name="hafiz_quran" value="Yes"> <small>Yes</small>
                          <input type="radio" name="hafiz_quran" value="No"> <small>No</small>
                        </div>
                      </div>
                      <div class="mb-2 border-bottom pb-2">
                        <div class="d-flex justify-content-between">
                          <label class="fw-bold small">Transport:</label>
                          <div id="trans_box">
                            <input type="radio" name="transport" value="Yes" class="tr_toggle"> <small>Yes</small>
                            <input type="radio" name="transport" value="No" class="tr_toggle"> <small>No</small>
                          </div>
                        </div>
                        <select name="route_id" id="sel_route" class="form-control form-control-sm mt-1" disabled></select>
                      </div>
                      <label class="fw-bold small">Interests / Sports:</label>
                      <div id="interests_box" class="d-flex flex-wrap gap-1">
                        <?php $ints = ['Cricket', 'Volleyball', 'Chess', 'Taekwondo', 'Scrabble', 'Skating'];
                        foreach ($ints as $i): ?>
                          <div class="custom-control custom-checkbox mr-2">
                            <input type="checkbox" name="interests[]" class="custom-control-input" id="int_<?= $i ?>" value="<?= $i ?>">
                            <label class="custom-control-label small" for="int_<?= $i ?>"><?= $i ?></label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <div class="office-use-box shadow-sm d-flex flex-column justify-content-between">
                      <div><label class="fw-bold small">Official Remarks:</label><textarea name="remarks" id="remarks" class="form-control" rows="5"></textarea></div>
                      <div class="text-right mt-3">
                        <div style="border-top: 1.5px solid #333; width: 180px; display: inline-block;">
                          <p class="text-center fw-bold mb-0 small">Auth. Signature</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="text-center mt-4"><button class="btn btn-primary btn-lg px-5 shadow" type="submit" name="update_student">UPDATE RECORD</button></div>
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

    /**
     * MAIN DATA SYNC
     */
    function initEditPage() {
      $('#sync_msg').show();
      $.getJSON('?action=fetch_student_full&id=' + "<?= $_GET['id'] ?>", function(s) {

        // 1. Basic Inputs
        $('#reg_no').val(s.reg_no);
        $('#admission_date').val(s.admission_date);
        $('#student_name').val(s.student_name);
        $('#cnic_bform').val(s.cnic_bform);
        $('#dob').val(s.dob);

        // Gender Fix
        let g = s.gender ? s.gender.toUpperCase() : '';
        $('#gender').val(g);

        $('#medium').val(s.medium ? s.medium : 'ENGLISH');
        $('#mother_language').val(s.mother_language);
        $('#caste').val(s.caste);
        $('#tehsil').val(s.tehsil);
        $('#district').val(s.district);
        $('#contact_no').val(s.student_contact);
        $('#student_address').val(s.student_address);

        // 2. Guardian
        $('#guardian_name').val(s.guardian_name);
        $('#relation').val(s.relation);
        $('#guardian_cnic').val(s.guardian_cnic);
        $('#guardian_contact').val(s.guardian_contact);
        $('#occupation').val(s.occupation);
        $('#guardian_address').val(s.guardian_address);

        // Checkbox Logic
        if (s.student_address == s.guardian_address && s.student_address != "") {
          $('#same_address').prop('checked', true);
          $('#guardian_address').prop('readonly', true);
        }

        // 3. School
        $('#prev_school_name').val(s.prev_school_name);
        $('#last_class').val(s.last_class);
        $('#passing_year').val(s.passing_year);
        $('#board_name').val(s.board_name);
        $('#remarks').val(s.remarks);

        // 4. Images
        if (s.student_photo) $('#photoPreview').attr('src', s.student_photo);
        if (s.cnic_doc) $('#prev_cnic').attr('src', s.cnic_doc);
        if (s.guardian_cnic_front) $('#prev_gf').attr('src', s.guardian_cnic_front);
        if (s.guardian_cnic_back) $('#prev_gb').attr('src', s.guardian_cnic_back);
        if (s.result_card_doc) $('#prev_rc').attr('src', s.result_card_doc);

        // 5. Radios
        $(`input[name="transport"][value="${s.transport}"]`).prop('checked', true).trigger('change');
        $(`input[name="hafiz_quran"][value="${s.hafiz_quran}"]`).prop('checked', true);
        $(`input[name="disability"][value="${s.disability}"]`).prop('checked', true);
        if (s.interests) {
          s.interests.split(',').forEach(i => $(`input[name="interests[]"][value="${i}"]`).prop('checked', true));
        }

        // 6. Dropdowns
        loadSessions(s.session);
        $('#sel_class').val(s.class_id);
        loadSections(s.class_id, s.section_id);
        $('#sel_group').val(s.subject_group_id).trigger('change');

        if (s.transport === 'Yes') {
          $('#sel_route').prop('disabled', false);
          loadRoutes(s.route_id);
        } else {
          loadRoutes();
        }
        $('#sync_msg').fadeOut();
      });
    }

    function loadSessions(targetID) {
      $.getJSON('?action=get_all_sessions', function(res) {
        let h = '<option value="">Select Session</option>';
        res.data.forEach(x => h += `<option value="${x.id}" ${x.id == targetID ? 'selected' : ''}>${x.session_name}</option>`);
        $('#session_select').html(h);
      });
    }

    function loadSections(classID, targetID) {
      if (!classID) return;
      $.getJSON('?action=fetch_sections&class_id=' + classID, function(data) {
        let h = '<option value="">Section</option>';
        data.forEach(x => h += `<option value="${x.id}" ${x.id == targetID ? 'selected' : ''}>${x.section_name}</option>`);
        $('#sel_section').html(h);
      });
    }

    function loadRoutes(targetID = null) {
      $.getJSON('?action=fetch_routes', function(data) {
        let h = '<option value="">Select Route</option>';
        data.forEach(x => h += `<option value="${x.id}" ${x.id == targetID ? 'selected' : ''}>${x.route_name}</option>`);
        $('#sel_route').html(h);
      });
    }

    // --- CAM FUNCTIONS ---
    async function startCam(id) {
      if (activeStream) stopActiveStream();
      const form = $('#registrationForm');
      form.find('.edit-cam-vid').hide();
      form.find('.edit-cam-img').show();
      form.find('[id^="cap_btn_"]').hide();

      const video = document.getElementById('webcam_' + id);
      const prevImg = document.getElementById(id === 'main' ? 'photoPreview' : 'prev_' + id);
      const capBtn = document.getElementById('cap_btn_' + id);

      try {
        activeStream = await navigator.mediaDevices.getUserMedia({
          video: {
            width: 1280,
            height: 720
          }
        });
        video.srcObject = activeStream;
        $(prevImg).hide();
        $(video).show();
        $(capBtn).show();
      } catch (err) {
        alert("Cam Access Denied");
      }
    }

    function capturePic(id, isPortrait) {
      const video = document.getElementById('webcam_' + id);
      const canvas = document.getElementById('hidden_canvas');
      const prevImg = document.getElementById(id === 'main' ? 'photoPreview' : 'prev_' + id);
      const hidInput = document.getElementById('hid_' + id);

      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      const ctx = canvas.getContext('2d');
      if (isPortrait) {
        const targetRatio = 0.8;
        let cropWidth = video.videoHeight * targetRatio;
        let startX = (video.videoWidth - cropWidth) / 2;
        canvas.width = cropWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, startX, 0, cropWidth, video.videoHeight, 0, 0, cropWidth, video.videoHeight);
      } else {
        ctx.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
      }

      const data = canvas.toDataURL('image/png');
      prevImg.src = data;
      hidInput.value = data;
      stopActiveStream();
      $(video).hide();
      $(prevImg).show();
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
      $('#cnic_bform, #guardian_cnic').inputmask("99999-9999999-9");
      $('#contact_no, #guardian_contact').inputmask("0399-9999999");

      $('#sel_class').change(function() {
        loadSections($(this).val());
      });

      $('#sel_group').change(function() {
        let gid = $(this).val();
        if (!gid) return;
        $.getJSON('?action=fetch_group_subjects&group_id=' + gid, function(data) {
          let h = '<ul class="d-flex justify-content-around list-unstyled w-100 m-0">';
          data.subjects.forEach(s => h += `<li class="badge-subject-item">${s}</li>`);
          h += '</ul>';
          $('#group_subjects_view').html(h);
        });
      });

      $('.tr_toggle').change(function() {
        if ($(this).val() == 'Yes') {
          $('#sel_route').prop('disabled', false);
          if ($('#sel_route').children().length <= 1) loadRoutes();
        } else {
          $('#sel_route').prop('disabled', true).val('');
        }
      });

      $('#same_address').change(function() {
        if ($(this).is(':checked')) {
          $('#guardian_address').val($('#student_address').val()).prop('readonly', true);
        } else {
          $('#guardian_address').prop('readonly', false);
        }
      });
      $('#student_address').keyup(function() {
        if ($('#same_address').is(':checked')) {
          $('#guardian_address').val($(this).val());
        }
      });

      // SYNC DATA
      initEditPage();

      // --- SWEETALERT ONLY ON URL PARAMETER ---
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('status') === 'success') {
        Swal.fire({
          title: 'Updated!',
          text: 'Student record saved successfully.',
          icon: 'success'
        }).then(() => {
          // Optional: Remove 'status=success' from URL without reloading
          const newUrl = window.location.pathname + '?id=' + "<?= $_GET['id'] ?>";
          window.history.replaceState(null, null, newUrl);
        });
      }

      <?php if (isset($error) && $error): ?>
        Swal.fire('Error!', '<?= addslashes($error) ?>', 'error');
      <?php endif; ?>
    });
  </script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
</body>

</html>