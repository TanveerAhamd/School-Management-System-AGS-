<?php

/**
 * 1. DATABASE CONNECTION & AUTHENTICATION
 */
require_once 'auth.php'; // Ensure this file has your $pdo connection

/**
 * 2. AJAX HANDLERS
 * Used for dynamic dropdowns and subjects display
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
    // Backend Validation: Uniqueness check for Registration Number
    $check = $pdo->prepare("SELECT id FROM students WHERE reg_no = ?");
    $check->execute([$_POST['reg_no']]);
    if ($check->fetch()) {
      throw new Exception("The Registration Number '{$_POST['reg_no']}' is already assigned to another student.");
    }

    $reg = $_POST['reg_no'];
    $folder = 'uploads/';
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    /**
     * Media Helper Function
     * Checks if a Camera Capture (Base64) or a File Upload exists
     */
    function handleMedia($fileKey, $camKey, $prefix, $reg, $folder)
    {
      $date_time = date('Ymd_His');
      if (!empty($_POST[$camKey]) && strpos($_POST[$camKey], 'base64') !== false) {
        $data = explode(',', $_POST[$camKey])[1];
        $fName = $prefix . "_" . $reg . "_" . $date_time . ".png";
        file_put_contents($folder . $fName, base64_decode($data));
        return $folder . $fName;
      } elseif (!empty($_FILES[$fileKey]['name'])) {
        $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
        $fName = $prefix . "_" . $reg . "_" . $date_time . "." . $ext;
        move_uploaded_file($_FILES[$fileKey]['tmp_name'], $folder . $fName);
        return $folder . $fName;
      }
      return null;
    }

    $photo = handleMedia('student_photo', 'cam_photo_data', 'PHOTO', $reg, $folder);
    $cnic_img = handleMedia('cnic_doc', 'cam_cnic_data', 'CNIC', $reg, $folder);
    $gf = handleMedia('guardian_cnic_front', 'cam_gf_data', 'GFRONT', $reg, $folder);
    $gb = handleMedia('guardian_cnic_back', 'cam_gb_data', 'GBACK', $reg, $folder);
    $rc = handleMedia('result_card_doc', 'cam_rc_data', 'RESULT', $reg, $folder);

    if (empty($photo) && empty($_POST['cam_photo_data'])) throw new Exception("Student Passport Photo is mandatory!");

    // SQL Insertion
    $sql = "INSERT INTO students (
            reg_no, admission_date, session, class_id, section_id, subject_group_id, 
            student_name, cnic_bform, dob, gender, mother_language, cast, 
            contact_no, address, guardian_name, relation, occupation, guardian_contact, 
            prev_school_name, last_class, passing_year, board_name, disability, 
            hafiz_quran, transport, route_id, interests, remarks, 
            student_photo, cnic_doc, guardian_cnic_front, guardian_cnic_back, result_card_doc, created_at
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $reg,
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
      $_POST['cast'],
      $_POST['contact_no'],
      $_POST['address'],
      $_POST['guardian_name'],
      $_POST['relation'],
      $_POST['occupation'],
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

// Pre-fetch Dropdowns
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$groups_list = $pdo->query("SELECT * FROM subject_groups")->fetchAll();
?>

?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Student Registration - Amina Girls Degree College</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/css/app.min.css">
  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <!-- Custom style CSS -->
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />
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
    }

    .doc-container {
      height: 100px;
      overflow: hidden;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
    }
  </style>
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <!-- include navbar form include folder -->
      <?php include 'include/navbar.php'; ?>

      <div class="main-sidebar sidebar-style-2">
        <!-- include asidebar form include folder -->
        <?php include 'include/asidebar.php'; ?>
      </div>
      <!-- Main Content -->
      <div class="main-content">

        <section class="section">
          <!-- breadcrumb -->
          <div class="row bg-title">
            <div class="col-12">
              <div class="card mb-3">
                <div class="card-body py-2 b-0">
                  <div class="d-flex flex-wrap align-items-center justify-content-between">

                    <!-- LEFT SIDE -->
                    <div class="mb-2 mb-md-0">
                      <h5 class="page-title mb-0"> </i> Manage Student (Add)</h5>
                    </div>

                    <!-- RIGHT SIDE -->
                    <div>
                      <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 bg-transparent p-0">
                          <li class="breadcrumb-item">
                            <a href="#"><i class="fas fa-tachometer-alt"></i> Home</a>
                          </li>
                          <li class="breadcrumb-item">
                            <a href="#"><i class="far fa-file"></i> Student</a>
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

          <form id="registrationForm" method="POST" enctype="multipart/form-data">
            <div class="card p-2 p-md-4">
              <div class="card-body rounded" style="border: 5px solid #0000004c !important ">
                <div class="row ">
                  <div class=" col-12 col-lg-10 text-center">
                    <div class="row ">
                      <div class="col-md-2">
                        <div class="d-flex align-items-end justify-content-between justify-content-md-center flex-wrap">
                          <img src="./assets/img/agslogo.png" alt="Logo" class="logo-img">
                          <div class="d-flex d-inline-block flex-wrap-0 flex-wrap-reverse justify-content-center mt-0 mt-md-4 text-right">
                            <label class="mb-0 ps-2 d-inline-block fw-bold text-center">Reg #: <span class="text-danger">*</span></label>
                            <input type="text" name="reg_no" value="<?= htmlspecialchars($_POST['reg_no'] ?? '') ?>" style="width: 100px;" class="form-control form-control-sm border-0 border-bottom rounded-0" placeholder="____________" required>
                          </div>
                          <div class="mt-0 px-2 d-none d-md-flex align-items-end text-right">
                            <input type="date" name="admission_date" value="<?= $_POST['admission_date'] ?? date('Y-m-d') ?>" class="form-control form-control-sm border-0 border-bottom rounded-0 p-0 text-center" style="width: 100px;">
                          </div>
                        </div>
                      </div>

                      <div class="col-md-10">
                        <h2 class="text-center m-0">Amina Girls Degree College</h2>
                        <div class="text-center">
                          <span class="">Address: Gailywal 21-MPR lodhran</span><br>
                          <h6 class="mt-2 rounded bg-primary px-3 py-1 d-inline-block text-white text-center mb-0 font-weight-bold">Application Form For Registration</h6>
                        </div>
                        <div class="text-center my-3">
                          <div class="border-primary d-flex gap-1 d-inline-block flex-wrap justify-content-around text-center">
                            <div class="mt-0 px-2 d-flex align-items-end text-right">
                              <label class="mb-0 fw-bold me-2">Session: <span class="text-danger">*</span></label>
                              <select name="session_id" id="session_select" class="form-select form-select-sm d-inline-block border-0 border-bottom rounded-0 p-0 text-center" style="width: 80px;" required>
                                <option value="">Select</option>
                              </select>
                            </div>
                            <div class="mt- px-2 d-flex align-items-end text-right">
                              <label class="mb-0 fw-bold me-2">Class: <span class="text-danger">*</span></label>
                              <select name="class_id" id="sel_class" class="form-select form-select-sm d-inline-block border-0 border-bottom rounded-0 p-0 text-center" style="width: 80px;" required>
                                <option value="">Select</option>
                                <?php foreach ($classes as $c): ?>
                                  <option value="<?= $c['id'] ?>" <?= (($_POST['class_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= $c['class_name'] ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="mt- px-2 d-flex align-items-end text-right">
                              <label class="mb-0 fw-bold me-2">Section: <span class="text-danger">*</span></label>
                              <select name="section_id" id="sel_section" class="form-select form-select-sm d-inline-block border-0 border-bottom rounded-0 p-0 text-center" style="width: 80px;" required>
                                <option value="">Section</option>
                              </select>
                            </div>

                            <div class="mt-0 px-2 d-flex align-items-end text-right">
                              <label class="mb-0 fw-bold me-2">Group: <span class="text-danger">*</span></label>
                              <select name="subject_group_id" id="sel_group" class="form-select form-select-sm d-inline-block border-0 border-bottom rounded-0 p-0 text-center" style="width: 80px;" required>
                                <option value="">Select</option>
                                <?php foreach ($groups_list as $g): ?>
                                  <option value="<?= $g['id'] ?>" <?= (($_POST['subject_group_id'] ?? '') == $g['id']) ? 'selected' : '' ?>><?= $g['group_name'] ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                          </div>
                          <div class="mt-2">
                            <div id="group_subjects_view" class="d-flex justify-content-around p-2 rounded border" style="background-color: #2727271e; min-height:40px;"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-12 col-lg-2 text-center">
                    <div class="border p-2 mb-2 rounded bg-light" style="position:relative; height: 140px; overflow:hidden;">
                      <video id="webcam_main" autoplay playsinline style="height: 100%; width: 100%; object-fit: cover; display: none;"></video>
                      <img id="photoPreview" src="<?= !empty($_POST['cam_photo_data']) ? $_POST['cam_photo_data'] : 'assets/img/userdummypic.png' ?>" style="height: 100%; width: 100%; object-fit: cover;">
                      <button type="button" id="cap_btn_main" class="btn btn-sm btn-danger position-absolute" style="bottom: 10px; left: 50%; transform: translateX(-50%); display: none; z-index:10;" onclick="capturePic('main')"><i class="fa fa-circle"></i></button>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                      <input type="hidden" name="cam_photo_data" id="hid_main" value="<?= htmlspecialchars($_POST['cam_photo_data'] ?? '') ?>">
                      <input type="file" name="student_photo" id="manualFile" accept="image/*" style="display:none;" onchange="previewManual(this, 'photoPreview', 'hid_main')">
                      <button type="button" class="btn btn-sm btn-success w-100" onclick="startCam('main')"><i class="fa fa-camera"></i></button>
                      <button type="button" class="btn btn-sm btn-info w-100" onclick="document.getElementById('manualFile').click()"><i class="fa fa-upload"></i></button>
                    </div>
                  </div>
                </div>

                <!-- STUDENT INFO -->
                <div class="row mb-4">
                  <div class="col-12">
                    <h6 class="text-dark-subtle border-bottom pb-1 mt-4"><i class='fas fa-user-circle'></i> Student Information</h6>
                    <div class="row">
                      <div class="col-md-3 mt-2"><label>Student Name <span class="text-danger">*</span></label><input type="text" name="student_name" value="<?= htmlspecialchars($_POST['student_name'] ?? '') ?>" class="form-control" required></div>
                      <div class="col-md-3 mt-2"><label>CNIC / B-Form <span class="text-danger">*</span></label><input type="text" name="cnic_bform" id="mask_cnic" value="<?= htmlspecialchars($_POST['cnic_bform'] ?? '') ?>" class="form-control" required></div>
                      <div class="col-md-3 mt-2"><label>Date of Birth <span class="text-danger">*</span></label><input type="date" name="dob" value="<?= $_POST['dob'] ?? '' ?>" class="form-control" required></div>
                      <div class="col-md-3 mt-2"><label>Gender <span class="text-danger">*</span></label><select name="gender" class="form-control">
                          <option value="Female" <?= (($_POST['gender'] ?? '') == 'Female' ? 'selected' : '') ?>>Female</option>
                          <option value="Male" <?= (($_POST['gender'] ?? '') == 'Male' ? 'selected' : '') ?>>Male</option>
                        </select></div>
                      <div class="col-md-2 mt-2"><label>Mother language</label><input type="text" name="mother_language" value="<?= htmlspecialchars($_POST['mother_language'] ?? '') ?>" class="form-control"></div>
                      <div class="col-md-2 mt-2"><label>Cast</label><input type="text" name="cast" value="<?= htmlspecialchars($_POST['cast'] ?? '') ?>" class="form-control"></div>
                      <div class="col-md-3 mt-2"><label>Contact #</label><input type="text" name="contact_no" id="mask_contact" value="<?= htmlspecialchars($_POST['contact_no'] ?? '') ?>" class="form-control"></div>
                      <div class="col-md-5 mt-2"><label>Address</label><input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" class="form-control"></div>
                    </div>
                  </div>
                </div>

                <!-- GUARDIAN INFO -->
                <div class="row mb-4">
                  <div class="col-12">
                    <h6 class="text-dark-subtle border-bottom my-2 py-2"><i class='fas fa-users'></i> Guardian Information</h6>
                    <div class="row">
                      <div class="col-md-3 mt-2"><label>Guardian Name <span class="text-danger">*</span></label><input type="text" name="guardian_name" value="<?= htmlspecialchars($_POST['guardian_name'] ?? '') ?>" class="form-control" required></div>
                      <div class="col-md-3 mt-2"><label>Relation <span class="text-danger">*</span></label><select name="relation" class="form-control"><?php $rels = ['Father', 'Mother', 'Uncle', 'Brother'];
                                                                                                                                                        foreach ($rels as $r) echo "<option value='$r' " . (($_POST['relation'] ?? '') == $r ? 'selected' : '') . ">$r</option>"; ?></select></div>
                      <div class="col-md-3 mt-2"><label>Occupation</label><input type="text" name="occupation" value="<?= htmlspecialchars($_POST['occupation'] ?? '') ?>" class="form-control"></div>
                      <div class="col-md-3 mt-2"><label>Contact# <span class="text-danger">*</span></label><input type="text" name="guardian_contact" id="mask_g_contact" value="<?= htmlspecialchars($_POST['guardian_contact'] ?? '') ?>" class="form-control" required></div>
                    </div>
                  </div>
                </div>

                <!-- PREVIOUS SCHOOL -->
                <div class="row rounded" style="background-color: rgba(223, 223, 223, 0.758);">
                  <div class="col-12 px-3">
                    <h6 class="text-dark-subtle border-bottom pt-2"><i class='fas fa-university'></i> Previous School Information</h6>
                    <div class="row pb-3">
                      <div class="col-md-5 mt-2"><label>School Name</label><input type="text" name="prev_school_name" value="<?= htmlspecialchars($_POST['prev_school_name'] ?? '') ?>" class="form-control"></div>
                      <div class="col-md-2 mt-2"><label>Last Class</label><input type="text" name="last_class" value="<?= htmlspecialchars($_POST['last_class'] ?? '') ?>" class="form-control"></div>
                      <div class="col-md-2 mt-2"><label>Passing Year</label><input type="text" name="passing_year" value="<?= htmlspecialchars($_POST['passing_year'] ?? '') ?>" class="form-control"></div>
                      <div class="col-md-3 mt-2"><label>Board Name</label><input type="text" name="board_name" value="<?= htmlspecialchars($_POST['board_name'] ?? '') ?>" class="form-control"></div>
                      <div class="col-md-12 mt-3">
                        <div class="form-check d-flex align-items-center gap-2">
                          <input class="form-check-input" type="checkbox" id="declareCheck" style="width: 20px; height: 20px;" required>
                          <label class="form-check-label fw-bold" for="declareCheck">&nbsp; &nbsp; I declare that the information provided is correct.</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- DOCUMENTS SECTION -->
                <div class="row mt-4">
                  <div class="col-12">
                    <h6 class="text-dark-subtle border-bottom pb-1"><i class='fas fa-file-alt'></i> Documents Upload</h6>
                    <div class="row">
                      <?php
                      $doc_fields = [
                        ['id' => 'cnic', 'label' => 'Student CNIC Img', 'hid' => 'cam_cnic_data', 'file' => 'cnic_doc'],
                        ['id' => 'gf', 'label' => 'Guardian Front', 'hid' => 'cam_gf_data', 'file' => 'guardian_cnic_front'],
                        ['id' => 'gb', 'label' => 'Guardian Back', 'hid' => 'cam_gb_data', 'file' => 'guardian_cnic_back'],
                        ['id' => 'rc', 'label' => 'Result Card', 'hid' => 'cam_rc_data', 'file' => 'result_card_doc'],
                      ];
                      foreach ($doc_fields as $df):
                      ?>
                        <div class="col-md-3 mt-2 text-center">
                          <label class="fw-bold small"><?= $df['label'] ?></label>
                          <div class="border bg-light doc-container">
                            <video id="webcam_<?= $df['id'] ?>" autoplay playsinline style="height: 100%; width: 100%; object-fit: cover; display: none;"></video>
                            <img id="prev_<?= $df['id'] ?>" src="<?= !empty($_POST[$df['hid']]) ? $_POST[$df['hid']] : 'assets/img/elementor.png' ?>" class="img-fluid" style="max-height: 100%;">
                            <button type="button" id="cap_btn_<?= $df['id'] ?>" class="btn btn-sm btn-danger position-absolute" style="top:5px; right:5px; display:none; z-index:10;" onclick="capturePic('<?= $df['id'] ?>')"><i class="fas fa-check"></i></button>
                          </div>
                          <div class="input-group input-group-sm mt-1">
                            <input type="hidden" name="<?= $df['hid'] ?>" id="hid_<?= $df['id'] ?>" value="<?= htmlspecialchars($_POST[$df['hid']] ?? '') ?>">
                            <input type="file" name="<?= $df['file'] ?>" class="form-control" onchange="previewManual(this, 'prev_<?= $df['id'] ?>', 'hid_<?= $df['id'] ?>')">
                            <button class="btn btn-success" type="button" onclick="startCam('<?= $df['id'] ?>')"><i class="fas fa-camera"></i></button>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>

                <!-- OFFICE USE -->
                <div class="row mt-4">
                  <div class="col-md-4 border rounded p-3">
                    <div class="d-flex justify-content-between mb-1"><label class="fw-bold">Disability:</label>
                      <div><input type="radio" name="disability" value="Yes" <?= (($_POST['disability'] ?? '') == 'Yes' ? 'checked' : '') ?>> Yes <input type="radio" name="disability" value="No" <?= (($_POST['disability'] ?? 'No') == 'No' ? 'checked' : '') ?>> No</div>
                    </div>
                    <div class="d-flex justify-content-between mb-1"><label class="fw-bold">Hafiz-Quran:</label>
                      <div><input type="radio" name="hafiz_quran" value="Yes" <?= (($_POST['hafiz_quran'] ?? '') == 'Yes' ? 'checked' : '') ?>> Yes <input type="radio" name="hafiz_quran" value="No" <?= (($_POST['hafiz_quran'] ?? 'No') == 'No' ? 'checked' : '') ?>> No</div>
                    </div>
                    <div class="d-flex justify-content-between mb-2 border-bottom pb-1"><label class="fw-bold">Transport:</label>
                      <div><input type="radio" name="transport" value="Yes" class="tr_toggle" <?= (($_POST['transport'] ?? '') == 'Yes' ? 'checked' : '') ?>> Yes <input type="radio" name="transport" value="No" class="tr_toggle" <?= (($_POST['transport'] ?? 'No') == 'No' ? 'checked' : '') ?>> No</div>
                    </div>
                    <select name="route_id" id="sel_route" class="form-control mt-2" <?= (($_POST['transport'] ?? '') != 'Yes' ? 'disabled' : '') ?>></select>
                    <label class="fw-bold d-block mt-3">Interests:</label>
                    <div class="d-flex flex-wrap gap-2">
                      <?php $ints = ['Cricket', 'Volleyball', 'Chess', 'Taekwondo', 'Scrabble', 'Skating'];
                      $posted_ints = $_POST['interests'] ?? [];
                      if (!is_array($posted_ints)) $posted_ints = explode(',', $posted_ints);
                      foreach ($ints as $i): ?>
                        <label class="mx-1 small"><input type="checkbox" name="interests[]" value="<?= $i ?>" <?= in_array($i, $posted_ints) ? 'checked' : '' ?>> <?= $i ?></label>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <div class="col-md-8 border rounded p-3">
                    <label class="fw-bold">Remarks:</label><textarea name="remarks" class="form-control" rows="5"><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
                    <div class="text-right mt-3">
                      <div style="border-top: 2px solid #000; width: 200px; display: inline-block;">
                        <p class="text-center fw-bold mb-0">Authority (Signature)</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="text-center mt-4"><button class="btn btn-primary btn-lg px-5" type="submit" name="save_student">Save Registration</button></div>
              </div>
            </div>
          </form>
        </section>
      </div>
      <!-- footer -->
      <?php include 'include/footer.php'; ?>
    </div>
  </div>

  <canvas id="hidden_canvas" style="display:none;">bholybadsha</canvas>

  <!-- General JS Scripts -->
  <script src="assets/js/app.min.js"></script>
  <!-- JS Libraies -->
  <!-- Page Specific JS File -->
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.flash.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>
  <script src="assets/js/page/datatables.js"></script>
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <!-- Custom JS File -->
  <script src="assets/js/custom.js"></script>

  <script>
    let activeStream = null;

    async function startCam(id) {
      if (activeStream) stopActiveStream();
      document.querySelectorAll('video').forEach(v => v.style.display = 'none');
      document.querySelectorAll('img').forEach(i => i.style.display = 'block');
      document.querySelectorAll('[id^="cap_btn_"]').forEach(b => b.style.display = 'none');

      const video = document.getElementById('webcam_' + id);
      const prevImg = document.getElementById(id === 'main' ? 'photoPreview' : 'prev_' + id);
      const capBtn = document.getElementById('cap_btn_' + id);

      try {
        activeStream = await navigator.mediaDevices.getUserMedia({
          video: true
        });
        video.srcObject = activeStream;
        prevImg.style.display = 'none';
        video.style.display = 'block';
        capBtn.style.display = 'block';
      } catch (err) {
        Swal.fire('Error', 'Camera access denied!', 'error');
      }
    }

    function capturePic(id) {
      const video = document.getElementById('webcam_' + id);
      const canvas = document.getElementById('hidden_canvas');
      const prevImg = document.getElementById(id === 'main' ? 'photoPreview' : 'prev_' + id);
      const hidInput = document.getElementById('hid_' + id);

      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      canvas.getContext('2d').drawImage(video, 0, 0);
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
      $('#mask_cnic').inputmask("99999-9999999-9");
      $('#mask_contact, #mask_g_contact').inputmask("0399-9999999");

      $.getJSON('?action=get_all_sessions', function(res) {
        let h = '<option value="">Select</option>';
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
          let h = '';
          data.subjects.forEach(s => h += `<span class="badge-subject">${s}</span>`);
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
</body>


<!-- forms-validation.html  21 Nov 2019 03:55:16 GMT -->

</html>