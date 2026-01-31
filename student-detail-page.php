<?php

/**
 * 1. DATABASE CONNECTION & DATA FETCHING
 */
require_once 'auth.php';

if (!isset($_GET['id'])) {
  header("Location: student-list.php");
  exit;
}

$id = $_GET['id'];

/**
 * GLOBAL QUERY: "is_deleted = 0" condition removed to allow viewing 
 * Active, Passout, Dropout, and Archived students.
 */
$sql = "SELECT s.*, 
        c.class_name, 
        sec.section_name, 
        sess.session_name, 
        tr.route_name,
        sg.group_name
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN academic_sessions sess ON s.session = sess.id
        LEFT JOIN transport_routes tr ON s.route_id = tr.id
        LEFT JOIN subject_groups sg ON s.subject_group_id = sg.id
        WHERE s.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
  die("<div class='container mt-5'><div class='alert alert-danger'>Student record not found in the database.</div></div>");
}

/**
 * 2. FETCH SUBJECTS
 */
$subjects_list = "No subjects assigned";
if (!empty($student['subject_group_id'])) {
  $stmt_sub = $pdo->prepare("SELECT s.subject_name FROM subject_group_items sgi 
                               JOIN subjects s ON sgi.subject_id = s.id 
                               WHERE sgi.group_id = ?");
  $stmt_sub->execute([$student['subject_group_id']]);
  $subs = $stmt_sub->fetchAll(PDO::FETCH_COLUMN);
  if ($subs) {
    $subjects_list = implode(", ", $subs);
  }
}



function formatMyDate($date)
{
  return (!empty($date) && $date != '0000-00-00') ? date('d-M-Y', strtotime($date)) : 'N/A';
}
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
  <title>Detail View | <?= strtoupper(htmlspecialchars($student['student_name'])) ?></title>

  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="icon" href="assets/img/favicon.png">

  <style>
    .print-metadata {
      display: none;
    }

    /* Global Uppercase for Data */
    .val-text,
    .uppercase-data {
      text-transform: uppercase;
      color: #000 !important;
      font-weight: 900 !important;
    }

    .val-text {
      border-bottom: 1px solid #eee;
      display: inline-block;
      min-width: 50px;
    }

    /* Status Stamp for non-active students */
    .status-stamp {
      position: absolute;
      top: 40px;
      right: 180px;
      border: 4px solid;
      padding: 5px 20px;
      border-radius: 12px;
      font-weight: 900;
      font-size: 22px;
      transform: rotate(-15deg);
      opacity: 0.4;
      z-index: 100;
      text-transform: uppercase;
    }

    /* Image Box Styling */
    .img-box {
      border: 1px solid #ddd;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      overflow: hidden;
      cursor: pointer;
      transition: 0.3s;
    }

    .img-box:hover {
      border-color: #6777ef;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .img-box img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }

    /* --- A4 PRINT OPTIMIZATION --- */
    @media print {
      @page {
        size: A4;
        margin: 5mm;
      }

      body,
      html {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
      }

      .no-print,
      .bg-title,
      .main-footer,
      button,
      .loader,
      .navbar,
      .main-sidebar,
      .navbar-bg {
        display: none !important;
      }



      body * {
        visibility: hidden;
      }

      #printableCard,
      #printableCard *,
      #page2,
      #page2 *,
      #page3,
      #page3 *,
      .print-metadata,
      .print-metadata * {
        visibility: visible !important;
      }

      #page2,
      #page3 {
        page-break-before: always;
        display: block !important;
        position: relative !important;
        width: 100% !important;
      }

      .print-metadata {
        display: flex !important;
        justify-content: space-between;
        font-size: 10px;
        border-bottom: 1px solid #333;
        margin-bottom: 10px;
      }

      .row {
        display: flex !important;
        flex-wrap: wrap !important;
      }

      .col-md-2 {
        width: 16.66% !important;
      }

      .col-md-3 {
        width: 25% !important;
      }

      .col-md-4 {
        width: 33.33% !important;
      }

      .col-md-8 {
        width: 66.66% !important;
      }

      .col-md-6 {
        width: 50% !important;
      }

      .card-body.rounded {
        border: 2px solid #000 !important;
        padding: 15px !important;
        min-height: 270mm !important;
      }

      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
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
          <!-- breadcrumb -->
          <div class="row bg-title no-print">
            <div class="col-12">
              <div class="card mb-3">
                <div class="card-body py-2 b-0 d-flex justify-content-between align-items-center">
                  <h5 class="page-title mb-0">View Profile</h5>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">
                      <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                      <li class="breadcrumb-item active">Print</li>
                    </ol>
                  </nav>
                </div>
              </div>
            </div>
          </div>

          <!-- PAGE 01: MAIN FORM -->
          <div class="card p-2 p-md-4 border-0" id="printableCard">
            <!-- DYNAMIC STATUS STAMP -->
            <?php
            if ($student['is_passout'] == 1) echo '<div class="status-stamp text-success">PASSOUT</div>';
            elseif ($student['is_dropout'] == 1) echo '<div class="status-stamp text-danger">DROPOUT</div>';
            elseif ($student['is_deleted'] == 1) echo '<div class="status-stamp text-muted">ARCHIVED</div>';
            ?>

            <div class="print-metadata">
              <span class="uppercase-data">Student: <?= htmlspecialchars($student['student_name']) ?></span>
              <span><strong>ID:</strong> <?= $student['reg_no'] ?> | <strong>Print Date:</strong> <?= date('d-M-Y') ?></span>
            </div>

            <div class="card-body rounded" style="border: 5px solid #0000004c !important ">
              <div class="row align-items-center mb-4">
                <div class="col-md-2 text-center">
                  <!-- 1. Dynamic Logo -->
                  <picture class="d-flex justify-content-center">
                    <source media="(min-width: 576px)" srcset="<?= $sch_logo ?>">
                    <img src="<?= $sch_logo ?>" alt="School Logo" class="logo-img d-block" style="max-height: 90px; width: auto; object-fit: contain;">
                  </picture>
                  <div class="mt-2">
                    <small class="fw-bold d-block text-nowrap">Reg #: <span class="val-text"><?= $student['reg_no'] ?></span></small>
                    <small class="fw-bold d-block text-nowrap">Date: <span class="val-text"><?= formatMyDate($student['created_at']) ?></span></small>
                  </div>
                </div>

                <div class="col-md-8 text-center">
                  <!-- 2. Dynamic Title (Responsive) -->
                  <!-- <h5 class="d-md-none text-center text-nowrap my-2 font-weight-bold"><?= htmlspecialchars($sch_name) ?></h5> -->
                  <h2 class="text-center text-nowrap m-0 font-weight-bold" style="letter-spacing: 2px;">
                    <?= htmlspecialchars($sch_name) ?>
                  </h2>
                  <div class="text-center">
                    <span class="text-center text-muted  py-3">
                      <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($sch_address) ?>
                    </span>
                    <br>
                    <h6
                      class=" mt-2 rounded bg-primary px-3 py-2 my-2 d-inline-block text-white text-center mb-0 font-weight-bold">
                      Student Detial Record </h6>
                  </div>
                  <div class="d-flex justify-content-around mt-3 border-top border-bottom py-2">
                    <span><strong>Session:</strong> <span class="val-text"><?= $student['session_name'] ?></span></span>
                    <span><strong>Class:</strong> <span class="val-text"><?= $student['class_name'] ?></span></span>
                    <span><strong>Section:</strong> <span class="val-text"><?= $student['section_name'] ?></span></span>
                    <!-- ADDED MEDIUM -->
                    <span><strong>Medium:</strong> <span class="val-text"><?= $student['medium'] ?></span></span>
                    <span><strong>Group:</strong> <span class="val-text"><?= $student['group_name'] ?></span></span>
                  </div>
                </div>

                <div class="col-md-2 text-center">
                  <div class="img-box" style="height: 140px; width: 120px; background: #fff;" onclick="printSpecificImage(this.querySelector('img').src)">
                    <img src="<?= !empty($student['student_photo']) ? $student['student_photo'] : 'assets/img/userdummypic.png' ?>" title="Click to Print Photo">
                  </div>
                  <small class="no-print text-muted">Click to print</small>
                </div>
              </div>

              <div class="mt-2 mb-4">
                <div class="badge bg-light text-dark p-2 border w-100 text-center" style="white-space: normal; font-size: 13px;">
                  <strong style="color: #6777ef;">Assigned Subjects:</strong> <span class="val-text mx-2"><?= $subjects_list ?></span>
                </div>
              </div>
              <!-- <div class="mt-2 p-2 border rounded" style="background-color: #f9f9f9;">
                <strong style="color: #6777ef;">Assigned Subjects:</strong>

                <div class="d-flex flex-wrap justify-content-around mt-2">
                  <?php if (!empty($subjects_array)): ?>
                    <?php foreach ($subjects_array as $sub): ?>
                      <span class="badge bg-light text-dark border px-3 py-2 mb-1">
                        <?= htmlspecialchars($sub) ?>
                      </span>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span class="text-muted small">No subjects assigned</span>
                  <?php endif; ?>
                </div>
              </div> -->

              <!-- Student Info Section -->
              <div class="row mt-4">
                <div class="col-12 d-flex justify-content-between border-bottom pb-1">
                  <h6 class="fw-bold"><i class='fas fa-user-circle'></i> Student Information</h6>
                  <h6 dir="rtl" class="fw-bold">نام (اردو): ___________________</h6>
                </div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Student Name</label><span class="val-text"><?= $student['student_name'] ?></span></div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">B-Form / CNIC</label><span class="val-text"><?= $student['cnic_bform'] ?></span></div>
                <div class="col-md-2 mt-3"><label class="d-block small text-muted">DOB: MM/DD/YYYY</label><span class="val-text"><?= formatMyDate($student['dob']) ?></span></div>
                <div class="col-md-2 mt-3"><label class="d-block small text-muted">Gender</label><span class="val-text"><?= $student['gender'] ?></span></div>
                <div class="col-md-2 mt-3"><label class="d-block small text-muted">Mother Language</label><span class="val-text"><?= $student['mother_language'] ?></span></div>
                <div class="col-md-2 mt-3"><label class="d-block small text-muted">Caste</label><span class="val-text"><?= $student['caste'] ?></span></div>

                <!-- ADDED TEHSIL & DISTRICT -->
                <div class="col-md-2 mt-3"><label class="d-block small text-muted">Tehsil</label><span class="val-text"><?= $student['tehsil'] ?></span></div>
                <div class="col-md-2 mt-3"><label class="d-block small text-muted">District</label><span class="val-text"><?= $student['district'] ?></span></div>
                <div class="col-md-4 mt-3"><label class="d-block small text-muted">Address</label><span class="val-text"><?= $student['student_address'] ?></span></div>
                <div class="col-md-2 mt-3"><label class="d-block small text-muted">Contact #</label><span class="val-text"><?= $student['student_contact'] ?></span></div>

              </div>

              <!-- Guardian Section -->
              <div class="row mt-5">
                <div class="col-12 d-flex justify-content-between border-bottom pb-1">
                  <h6 class="fw-bold"><i class='fas fa-users'></i> Guardian Information</h6>
                  <h6 dir="rtl" class="fw-bold">سرپرست کا نام (اردو): ___________________</h6>
                </div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Guardian Name</label><span class="val-text"><?= $student['guardian_name'] ?></span></div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Relation</label><span class="val-text"><?= $student['relation'] ?></span></div>

                <!-- ADDED GUARDIAN CNIC -->
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Guardian CNIC</label><span class="val-text"><?= $student['guardian_cnic'] ?></span></div>

                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Contact #</label><span class="val-text"><?= $student['guardian_contact'] ?></span></div>
                <div class="col-md-4 mt-3"><label class="d-block small text-muted">Occupation</label><span class="val-text"><?= $student['occupation'] ?></span></div>

                <!-- ADDED GUARDIAN ADDRESS -->
                <div class="col-md-8 mt-3"><label class="d-block small text-muted">Guardian Address</label><span class="val-text w-100"><?= $student['guardian_address'] ?></span></div>
              </div>

              <!-- Previous School Section -->
              <div class="row rounded mt-4 py-4 " style="background-color: rgba(223, 223, 223, 0.5);">
                <div class="col-12">
                  <h6 class="text-dark border-bottom pb-1 fw-bold"><i class='fas fa-university'></i> Previous School Information</h6>
                  <div class="row pb-2">
                    <div class="col-md-4 mt-2">
                      <div class="d-flex flex-column">
                        <label class="small">School Name</label>
                        <p class="val-text"><?= $student['prev_school_name'] ?></p>
                      </div>
                    </div>
                    <div class="col-md-2 mt-2">
                      <div class="d-flex flex-column">
                        <label class="small">Last Class</label>
                        <p class="val-text"><?= $student['last_class'] ?></p>
                      </div>
                    </div>
                    <div class="col-md-2 mt-2">
                      <div class="d-flex flex-column">

                        <label class="small">Year</label>
                        <p class="val-text"><?= $student['passing_year'] ?></p>
                      </div>
                    </div>
                    <div class="col-md-3 mt-2">
                      <div class="d-flex flex-column">

                        <label class="small">Board</label>
                        <p class="val-text"><?= $student['board_name'] ?></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Office Use Section -->
              <div class="row mt-5 pt-3 rounded" style="background-color: #f8f9fa !important; border: 1px solid #ddd !important;">
                <div class="col-md-4 border-right">
                  <h6 class="fw-bold border-bottom pb-1">Office Use Checks</h6>
                  <p class="mb-1"><strong>Disability:</strong> <span class="uppercase-data"><?= $student['disability'] ?></span></p>
                  <p class="mb-1"><strong>Hafiz-e-Quran:</strong> <span class="uppercase-data"><?= $student['hafiz_quran'] ?></span></p>
                  <p class="mb-1"><strong>Transport:</strong> <span class="uppercase-data"><?= $student['transport'] ?> <?= ($student['transport'] == 'Yes') ? "({$student['route_name']})" : "" ?></span></p>
                  <p class="mb-1"><strong>Interests:</strong> <span class="uppercase-data"><?= $student['interests'] ?></span></p>
                </div>
                <div class="col-md-8">
                  <h6 class="fw-bold border-bottom pb-1">Remarks / Authority Note</h6>
                  <div class="p-2 border bg-white rounded uppercase-data" style="min-height: 70px; color: #000; font-size: 12px;">
                    <?= !empty($student['remarks']) ? $student['remarks'] : "VERIFIED RECORD." ?>
                  </div>
                  <div class="d-flex justify-content-end mt-4">
                    <div class="text-center" style="width: 180px; border-top: 1px solid #000;"><small class="fw-bold">Authority (Signature)</small></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PAGE 02: DOCUMENTS -->
          <div class="card p-2 p-md-4 border-0" id="page2">
            <div class="card-body rounded" style="min-height: 285mm; border: 1px solid #ccc !important; background: #fff;">
              <h5 class="text-center fw-bold border-bottom pb-2">PAGE 02: GUARDIAN CNIC & RESULT CARD</h5>
              <div class="row mt-4 text-center">
                <div class="col-md-6">
                  <h6 class="fw-bold small">GUARDIAN CNIC (FRONT)</h6>
                  <div class="img-box" style="height: 240px;" onclick="printSpecificImage(this.querySelector('img').src)">
                    <img src="<?= !empty($student['guardian_cnic_front']) ? $student['guardian_cnic_front'] : 'assets/img/elementor.png' ?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <h6 class="fw-bold small">GUARDIAN CNIC (BACK)</h6>
                  <div class="img-box" style="height: 240px;" onclick="printSpecificImage(this.querySelector('img').src)">
                    <img src="<?= !empty($student['guardian_cnic_back']) ? $student['guardian_cnic_back'] : 'assets/img/elementor.png' ?>">
                  </div>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col-12">
                  <h6 class="fw-bold border-bottom pb-1 text-center">PREVIOUS RESULT CARD / DMC</h6>
                  <div class="img-box" onclick="printSpecificImage(this.querySelector('img').src)">
                    <img src="<?= !empty($student['result_card_doc']) ? $student['result_card_doc'] : 'assets/img/elementor.png' ?>">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PAGE 03: B-FORM -->
          <div class="card p-2 p-md-4 border-0" id="page3">
            <div class="card-body rounded" style="min-height: 285mm; border: 1px solid #ccc !important; background: #fff;">
              <h5 class="text-center fw-bold border-bottom pb-2">PAGE 03: STUDENT B-FORM / CNIC</h5>
              <div class="row mt-4">
                <div class="col-12 text-center">
                  <div class="img-box" onclick="printSpecificImage(this.querySelector('img').src)">
                    <img src="<?= !empty($student['cnic_doc']) ? $student['cnic_doc'] : 'assets/img/elementor.png' ?>">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="text-center mt-5 no-print mb-5">
            <button type="button" class="btn btn-success btn-lg px-5 shadow mr-2" onclick="window.print()">
              <i class="fas fa-print"></i> Print Full Profile
            </button>
            <a href="student-edit-page.php?id=<?= $student['id'] ?>" class="btn btn-info btn-lg px-5 shadow">
              <i class="fas fa-edit"></i> Edit Record
            </a>
          </div>

        </section>
      </div>

      <?php include 'include/footer.php'; ?>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
  <script>
    function printSpecificImage(imgSrc) {
      if (imgSrc.includes('elementor.png') || imgSrc.includes('userdummypic.png')) {
        return;
      }
      var win = window.open('', '_blank');
      win.document.write('<html><body style="margin:0; display:flex; align-items:center; justify-content:center;"><img src="' + imgSrc + '" style="max-width:100%; max-height:100vh; object-fit:contain;"></body></html>');
      win.document.close();
      win.onload = function() {
        win.print();
        win.close();
      };
    }
    $(document).ready(function() {
      $('.loader').fadeOut('slow');
    });
  </script>
</body>

</html>