<?php
date_default_timezone_set('Asia/Karachi');
require_once 'auth.php';

// Global Filters Fetching
$classes = $pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC")->fetchAll();

$f_sess = $_GET['session_id'] ?? '';
$f_class = $_GET['class_id'] ?? '';
$f_stat = $_GET['student_status'] ?? 'All';

$where = ["1=1"];
$params = [];
if (!empty($f_sess)) {
  $where[] = "s.session = ?";
  $params[] = $f_sess;
}
if (!empty($f_class)) {
  $where[] = "s.class_id = ?";
  $params[] = $f_class;
}
if ($f_stat == 'Passout') $where[] = "s.is_passout = 1";
elseif ($f_stat == 'Dropout') $where[] = "s.is_dropout = 1";

$query = "SELECT s.*, c.class_name, sec.section_name, asess.session_name 
          FROM students s
          LEFT JOIN classes c ON s.class_id = c.id
          LEFT JOIN sections sec ON s.section_id = sec.id
          LEFT JOIN academic_sessions asess ON s.session = asess.id
          WHERE " . implode(" AND ", $where) . " ORDER BY s.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Certificates | AMINA Girls School</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <style>
    /* Certificate Box Setup */
    #certificate-area {
      width: 200mm;
      height: 297mm;
      padding: 15mm;
      border: 5px solid #000;
      background: #fff;
      margin: 0 auto;
      font-family: 'Times New Roman', serif;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      color: #000;
      position: relative;
      /* For watermark positioning */
      overflow: hidden;
    }

    /* WATERMARK LOGO */
    #certificate-area::before {
      content: "";
      background-image: url('assets/img/AGHS Logo.png');
      /* Aapka Logo Path */
      background-repeat: no-repeat;
      background-position: center;
      background-size: 450px;
      opacity: 0.08;
      /* Faintness */
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      z-index: 0;
    }

    .cert-header,
    .cert-title-heading,
    .cert-body,
    .cert-footer,
    .system-line {
      position: relative;
      z-index: 1;
      /* Content above watermark */
    }

    .cert-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 3px solid #000;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }

    .school-info {
      text-align: center;
      flex: 1;
    }

    .school-info h2 {
      font-size: 24px;
      font-weight: bold;
      margin: 0;
      text-transform: uppercase;
    }

    .cert-title-heading {
      font-size: 18px;
      font-weight: bold;
      text-align: center;
      text-decoration: underline;
      margin: 25px 0;
      text-transform: uppercase;
    }

    .cert-body {
      font-size: 20px;
      line-height: 2.1;
      text-align: justify;
      flex-grow: 1;
      color: #000;
    }

    .fill {
      border-bottom: 1px solid #000;
      font-weight: bold;
      padding: 0 10px;
      display: inline-block;
      min-width: 150px;
      text-align: center;
    }

    .blank-mode .fill {
      color: transparent !important;
    }

    .cert-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 40px;
    }

    .sign-box {
      text-align: center;
      width: 220px;
      border-top: 2px solid #000;
      padding-top: 5px;
      font-weight: bold;
      font-size: 17px;
    }

    /* SYSTEM FOOTER LINE */
    .system-line {
      margin-top: auto;
      border-top: 1px solid #ccc;
      display: flex;
      justify-content: space-between;
      font-size: 10px;
      color: #444;
      padding-top: 5px;
      font-family: Arial, sans-serif;
    }

    .court-tag {
      text-align: center;
      color: red;
      font-size: 11px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    /* PRINT SETTINGS - PERFECT CENTERING */
    @media print {
      @page {
        size: A4;
        margin: 0;
      }

      body * {
        visibility: hidden !important;
      }

      html,
      body {
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
      }

      #certificate-area,
      #certificate-area * {
        visibility: visible !important;
      }

      #certificate-area {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        width: 200mm !important;
        height: 297mm !important;
        margin: 0 !important;
        border: 5px solid #000 !important;
        display: flex !important;
        z-index: 9999;
      }

      .no-print {
        display: none !important;
      }
    }
  </style>
</head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="no-print"><?php include 'include/navbar.php'; ?></div>
      <div class="main-sidebar sidebar-style-2 no-print"><?php include 'include/asidebar.php'; ?></div>

      <div class="main-content">
        <section class="section">
          <div class="section-body no-print shadow-sm">
            <div class="row bg-title">
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-body py-2 b-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                      <h5 class="page-title mb-0">Print Certificate</h5>
                      <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 bg-transparent p-0">
                          <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                          <li class="breadcrumb-item active">Print Certificate</li>
                        </ol>
                      </nav>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card">
              <div class="card-header">
                <h4>Generate Student Certificates</h4>
              </div>
              <div class="card-body">
                <form method="GET" class="row mb-4">
                  <div class="col-md-3">
                    <select name="class_id" class="form-control select2 form-control-sm">
                      <option value="">All Classes</option>
                      <?php foreach ($classes as $c) echo "<option value='{$c['id']}' " . ($f_class == $c['id'] ? 'selected' : '') . ">{$c['class_name']}</option>"; ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <select name="student_status" class="form-control select2 form-control-sm">
                      <option value="All" <?= $f_stat == 'All' ? 'selected' : '' ?>>All Students</option>
                      <option value="Passout" <?= $f_stat == 'Passout' ? 'selected' : '' ?>>Passout Graduates</option>
                    </select>
                  </div>
                  <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm btn-block">Search</button></div>
                </form>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped" id="certTable">
                    <thead>
                      <tr>
                        <th>Reg#</th>
                        <th>Student Name</th>
                        <th>Guardian</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($students as $row): ?>
                        <tr>
                          <td><?= $row['reg_no'] ?></td>
                          <td class="font-weight-bold"><?= $row['student_name'] ?></td>
                          <td><?= $row['guardian_name'] ?></td>
                          <td><?= $row['class_name'] ?></td>
                          <td><?= $row['section_name'] ?></td>
                          <td>
                            <?php
                            if ($row['is_passout']) echo '<span class="badge badge-success">Passout</span>';
                            elseif ($row['is_dropout']) echo '<span class="badge badge-danger">Dropout</span>';
                            else echo '<span class="badge badge-info">Active</span>';
                            ?>
                          </td>

                          <td>
                            <?php if ($row['is_passout']): ?>
                              <!-- ACTIVE BUTTON -->
                              <button class="btn btn-dark btn-sm"
                                onclick='openCert(<?= json_encode($row) ?>)'>
                                Generate
                              </button>
                            <?php else: ?>
                              <!-- FADED + DISABLED BUTTON -->
                              <button class="btn btn-secondary btn-sm"
                                style="opacity:0.4; cursor:not-allowed; pointer-events:none;"
                                disabled
                                title="Certificate only for passout students">
                                Generate
                              </button>
                            <?php endif; ?>
                          </td>

                        </tr>

                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
      <?php include 'include/footer.php'; ?>

    </div>
  </div>

  <!-- MODAL -->
  <div class="modal fade" id="certModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-dark text-white no-print py-2">
          <h6 class="modal-title">Certificate Customization</h6>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="card p-2 mb-3 no-print border-0 shadow-sm bg-light">
            <div class="row align-items-center">
              <div class="col-md-4">
                <select id="certType" class="form-control form-control-sm" onchange="updateUI()">
                  <option value="character">Character Certificate (Amina School)</option>
                  <option value="passout">Graduate Certificate (Tareen Foundation)</option>
                </select>
              </div>
              <div id="pass_extra" class="col-md-4" style="display:none;">
                <input type="text" id="roll" class="form-control form-control-sm" placeholder="Enter Roll No" onkeyup="updateUI()">
                <input type="text" id="total_marks" class="form-control form-control-sm" placeholder="Enter Total Marks" onkeyup="updateUI()">
              </div>
              <div class="col-md-2">
                <div class="custom-control custom-checkbox small">
                  <input type="checkbox" id="blankToggle" class="custom-control-input">
                  <label class="custom-control-label" for="blankToggle">Blank Mode</label>
                </div>
              </div>
              <div class="col-md-2">
                <button class="btn btn-primary btn-sm btn-block" onclick="window.print()">Print A4</button>
              </div>
            </div>
          </div>

          <div id="certificate-area">
            <!-- Header -->
            <div class="cert-header">
              <img src="assets/img/AGHS Logo.png" width="75" onerror="this.src='https://via.placeholder.com/75'">
              <div class="school-info">
                <h2 id="headTitle">AMINA GIRLS HIGH SCHOOL</h2>
                <p class="m-0">21/MPR Lodhran</p>
              </div>
              <img src="assets/img/teflogo.png" width="100" onerror="this.src='https://via.placeholder.com/75'">
            </div>

            <!-- Session/Date -->
            <div class="d-flex justify-content-between mb-2 font-weight-bold" style="font-size: 18px;">
              <div>Session: <span class="fill" id="dispSess"></span></div>
              <div>Dated: <span class="fill" id="dispDate"></span></div>
            </div>

            <div class="cert-title-heading" id="midTitle">Character Certificate</div>
            <div class="cert-body" id="bodyDisp"></div>

            <div class="cert-footer">
              <div class="sign-box">Admin Incharge</div>
              <div class="sign-box">Principal Signature</div>
            </div>

            <div class="court-tag">*** THIS IS NOT VALID FOR COURT ***</div>

            <!-- Footer Line with Print Time -->
            <div class="system-line">
              <span>Amina Girls School Student Management System</span>
              <span>Print Date & Time: <?php echo date('d-M-Y h:i A'); ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/js/scripts.js"></script>

  <script>
    let student = {};

    function openCert(data) {
      student = data;
      updateUI();
      $('#certModal').modal('show');
    }

    function updateUI() {
      const type = $('#certType').val();
      const sess = student.session_name || '_________';
      const today = "<?= date('d-M-Y') ?>";

      $('#dispSess').text(sess);
      $('#dispDate').text(today);

      if (type === 'character') {
        $('#pass_extra').hide();
        $('#headTitle').text("AMINA GIRLS HIGH SCHOOL");
        $('#midTitle').text("Character Certificate");
        $('#bodyDisp').html(`It is certified that <span class="fill">${student.student_name}</span> D/O 
                <span class="fill">${student.guardian_name}</span>. She has been student of 
                <b>Amina Girls High School 21/MPR lodhran</b>. <br><br>
                Her date of birth as per record is <span class="fill">${student.dob || 'N/A'}</span>. She is studying in 
                <span class="fill">${student.class_name}</span> class now and her character and behaviour during 
                the period of study was smooth and excellent towards the teachers and all the school members. <br><br>
                We wish her all the best in life.`);
      } else {
        $('#pass_extra').show();
        $('#headTitle').text("Amina Girls High School");
        $('#midTitle').text("Secondary School Character Certificate");
        const roll = $('#roll').val() || '_________';
        const total = $('#total_marks').val() || '_________';
        $('#bodyDisp').html(`This is to certify that <span class="fill">${student.student_name}</span> daughter of 
                <span class="fill">${student.guardian_name}</span> in the secondary school certificate examination 
                of board of intermediate secondary education multan held in <span class="fill">${sess}</span> 
                under roll no <span class="fill">${roll}</span>. She secured marks out of <span class="fill">${total}</span>. <br><br>
                During her stay in this institute, her conduct was satisfactory.`);
      }
    }

    $('#blankToggle').change(function() {
      $('#certificate-area').toggleClass('blank-mode', $(this).is(':checked'));
    });

    $(document).ready(function() {
      $('#certTable').DataTable();
    });
  </script>
</body>

</html>