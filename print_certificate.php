<?php
date_default_timezone_set('Asia/Karachi');
require_once 'auth.php';

// 1. Database Queries
$classes = $pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC")->fetchAll();

$f_sess = $_GET['session_id'] ?? '';
$f_class = $_GET['class_id'] ?? '';
$f_stat = $_GET['student_status'] ?? 'Passout'; // Default Filter to Passout

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

// Strictly enforcing Passout check if needed or allowing filter
if ($f_stat == 'Passout') $where[] = "s.is_passout = 1";
elseif ($f_stat == 'Dropout') $where[] = "s.is_dropout = 1";
// Note: If you want to ONLY show passouts always, uncomment the line below:
// $where[] = "s.is_passout = 1"; 

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
  <title>Certificates | AGHS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="icon" href="assets/img/favicon.png">

  <style>
    /* =========================================
       PERFECT CENTER PRINT ENGINE
       ========================================= */
    @media print {
      @page {
        size: A4 portrait;
        margin: 0mm !important;
      }

      html,
      body {
        margin: 0 !important;
        padding: 0 !important;
        height: 100%;
        width: 100%;
        background: #fff !important;
      }

      body * {
        visibility: hidden !important;
      }

      .no-print,
      .modal-header,
      .modal-footer,
      .modal-custom-controls,
      .navbar,
      .main-sidebar,
      .settingSidebar,
      .modal-backdrop,
      .close {
        display: none !important;
      }

      #certificate-area,
      #certificate-area * {
        visibility: visible !important;
      }

      #certificate-area {
        position: absolute !important;
        left: 50% !important;
        top: 3% !important;
        transform: translate(-50%, 3%) !important;
        width: 210mm !important;
        height: 297mm !important;
        padding: 15mm !important;
        border: 15px double #000 !important;
        box-sizing: border-box !important;
        background: #fff !important;
        z-index: 99999 !important;
      }
    }

    /* BLANK MODE LOGIC */
    .blank-mode .fill {
      color: transparent !important;
      border-bottom: 1px dashed #000 !important;
    }

    /* SCREEN VIEW (MODAL) */
    .modal-body {
      background: #444;
      padding: 0 !important;
    }

    #certificate-area {
      width: 210mm;
      height: 297mm;
      padding: 15mm;
      border: 8px double #333;
      background: #fff;
      margin: 20px auto;
      font-family: 'Times New Roman', serif;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      color: #000;
      position: relative;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.7);
    }

    /* WATERMARK */
    #certificate-area::before {
      content: "";
      background-image: url('<?= htmlspecialchars($sch_logo) ?>');
      background-repeat: no-repeat;
      background-position: center;
      background-size: 500px;
      opacity: 0.07;
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      z-index: 0;
    }

    .cert-content {
      position: relative;
      z-index: 1;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .cert-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 4px solid #000;
      padding-bottom: 10px;
      margin-bottom: 20px;
    }

    .school-info {
      text-align: center;
      flex: 1;
    }

    .school-info h2 {
      font-weight: bold;
      margin: 0;
      text-transform: uppercase;
    }

    .cert-title-heading {
      font-size: 20px;
      font-weight: bold;
      text-align: center;
      text-decoration: underline;
      margin: 30px 0;
    }

    .cert-body {
      font-size: 22px;
      line-height: 2.2;
      text-align: justify;
      flex-grow: 1;
      color: #000;
    }

    .fill {
      border-bottom: 1px dashed #000;
      font-weight: bold;
      padding: 0 8px;
      display: inline-block;
      min-width: 130px;
      text-align: center;
    }

    .cert-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 50px;
    }

    .sign-box {
      text-align: center;
      width: 220px;
      border-top: 2px solid #000;
      padding-top: 8px;
      font-weight: bold;
      font-size: 17px;
    }

    .court-tag {
      text-align: center;
      color: red;
      font-size: 11px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .system-line {
      border-top: 1px solid #ccc;
      display: flex;
      justify-content: space-between;
      font-size: 10px;
      color: #666;
      padding-top: 5px;
      margin-top: 10px;
    }

    /* COMPACT FORM CONTROLS */
    .modal-custom-controls {
      background: #fff;
      padding: 10px 20px;
      border-bottom: 2px solid #333;
    }

    .modal-custom-controls label {
      font-size: 10px;
      font-weight: bold;
      margin-bottom: 0;
      text-transform: uppercase;
    }

    .modal-custom-controls .form-control-sm {
      height: 28px;
      font-size: 11px;
    }
  </style>
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="no-print"><?php include 'include/navbar.php'; ?></div>
      <div class="main-sidebar sidebar-style-2 no-print"><?php include 'include/asidebar.php'; ?></div>

      <div class="main-content">
        <section class="section">
          <div class="section-body no-print">
            <div class="card shadow-sm">
              <div class="card-header">
                <h4>Generate Certificates (Passout Only)</h4>
              </div>
              <div class="card-body">
                <form method="GET" class="row mb-4">
                  <div class="col-md-3">
                    <select name="class_id" class="form-control select2">
                      <option value="">All Classes</option>
                      <?php foreach ($classes as $c) echo "<option value='{$c['id']}' " . ($f_class == $c['id'] ? 'selected' : '') . ">{$c['class_name']}</option>"; ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <select name="student_status" class="form-control select2">
                      <option value="Passout" <?= $f_stat == 'Passout' ? 'selected' : '' ?>>Passout Graduates</option>
                      <option value="All" <?= $f_stat == 'All' ? 'selected' : '' ?>>All Students</option>
                      <option value="Dropout" <?= $f_stat == 'Dropout' ? 'selected' : '' ?>>Dropout</option>
                    </select>
                  </div>
                  <div class="col-md-2"><button type="submit" class="btn btn-primary btn-block">Search</button></div>
                </form>

                <div class="table-responsive">
                  <table class="table table-striped" id="certTable">
                    <thead>
                      <tr>
                        <th>Reg#</th>
                        <th>Name</th>
                        <th>Guardian</th>
                        <th>Class</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($students as $row): ?>
                        <tr>
                          <td><?= $row['reg_no'] ?></td>
                          <td class="font-weight-bold text-uppercase"><?= $row['student_name'] ?></td>
                          <td><?= $row['guardian_name'] ?></td>
                          <td><?= $row['class_name'] ?></td>
                          <td>
                            <?php
                            if ($row['is_passout']) echo '<span class="badge badge-success">Passout</span>';
                            elseif ($row['is_dropout']) echo '<span class="badge badge-danger">Dropout</span>';
                            else echo '<span class="badge badge-info">Active</span>';
                            ?>
                          </td>
                          <td>
                            <?php if ($row['is_passout']): ?>
                              <button class="btn btn-dark btn-sm" onclick='openCert(<?= json_encode($row) ?>)'>Generate</button>
                            <?php else: ?>
                              <button class="btn btn-secondary btn-sm" disabled title="Only for passout students">Generate</button>
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

      <!-- THE MODAL -->
      <div class="modal fade" id="basicModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content" style="border-radius: 0; border: none;">
            <div class="modal-header py-1 bg-dark text-white no-print" style="border-radius: 0;">
              <span class="modal-title small">Certificate Editor</span>
              <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <div class="modal-custom-controls no-print">
                <div class="row align-items-end gx-2">
                  <div class="col-md-2">
                    <label>Template</label>
                    <select id="certType" class="form-control form-control-sm" onchange="updateUI()">
                      <option value="passout">Passout Cert.</option>
                      <option value="character">Character Cert.</option>
                    </select>
                  </div>
                  <div class="col-md-6 row mb-0" id="pass_extra">
                    <div class="col-md-3"><label>Roll No</label><input type="text" id="roll" class="form-control form-control-sm" onkeyup="updateUI()"></div>
                    <div class="col-md-3"><label>Marks Obt.</label><input type="text" id="obt_marks" class="form-control form-control-sm" onkeyup="updateUI()"></div>
                    <div class="col-md-3"><label>Total Marks</label><input type="text" id="tot_marks" class="form-control form-control-sm" onkeyup="updateUI()"></div>
                    <div class="col-md-3"><label>Birth Date</label><input type="text" id="custom_dob" class="form-control form-control-sm" onkeyup="updateUI()"></div>
                  </div>
                  <div class="col-md-2 mb-1">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" id="blankMode">
                      <label class="custom-control-label" for="blankMode" style="font-size: 12px; cursor: pointer;">Blank Mode</label>
                    </div>
                  </div>
                  <div class="col-md-2 text-right">
                    <button class="btn btn-success btn-sm font-weight-bold btn-block" onclick="window.print()"><i class="fa fa-print"></i> PRINT A4</button>
                  </div>
                </div>
              </div>

              <!-- THE CERTIFICATE AREA -->
              <div id="certificate-area">
                <div class="cert-content">
                  <div class="cert-header">
                    <img src="<?= $sch_logo ?>" width="85">
                    <div class="school-info">
                      <!-- 2. Dynamic Title (Responsive) -->
                      <h2 class=" text-center text-nowrap m-0 font-weight-bold">
                        <?= htmlspecialchars($sch_name) ?>
                      </h2>
                      <div class="text-center">
                        <span class="text-center  py-3">
                          <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($sch_address) ?>
                        </span>
                        <br>
                        <p class="text-center text-dark font-weight-bold  mb-0">
                          <i class="fas fa-phone-alt text-success"></i> Contact: <?= htmlspecialchars($sch_contact) ?>
                        </p>
                      </div>
                    </div>
                    <img src="assets/img/teflogo.png" width="100">
                  </div>

                  <div class="d-flex justify-content-between mb-2 font-weight-bold" style="font-size: 20px;">
                    <div>Serial No: <span class="fill"><?= date('Y') ?>-<?= rand(100, 999) ?></span></div>
                    <div>Dated: <span class="fill"><?= date('d-M-Y') ?></span></div>
                  </div>

                  <div class="cert-title-heading" id="midTitle">Certificate</div>
                  <div class="cert-body" id="bodyDisp"></div>

                  <div class="cert-footer">
                    <div class="sign-box">Admin Incharge</div>
                    <div class="sign-box">Principal Signature</div>
                  </div>

                  <div class="court-tag">*** THIS DOCUMENT IS NOT VALID FOR ANY LEGAL COURT PROCEEDINGS ***</div>
                  <div class="system-line">
                    <span>Design & Developed by Tanveer Ahmad | Amina School SMS</span>
                    <span>Print Time: <?php echo date('d-M-Y h:i A'); ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php include 'include/footer.php'; ?>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/js/scripts.js"></script>

  <script>
    let student = {};

    function openCert(data) {
      student = data;
      $('#custom_dob').val(student.dob || '');
      $('#blankMode').prop('checked', false);
      $('#certificate-area').removeClass('blank-mode');
      updateUI();
      $('#basicModal').modal('show');
    }

    $('#blankMode').on('change', function() {
      if ($(this).is(':checked')) {
        $('#certificate-area').addClass('blank-mode');
      } else {
        $('#certificate-area').removeClass('blank-mode');
      }
    });

    function updateUI() {
      const type = $('#certType').val();
      const sess = student.session_name || '_________';
      const dob = $('#custom_dob').val() || '_________';

      const gender = (student.gender || 'Female').toLowerCase();
      const prefix = (gender === 'male') ? 'Mr.' : 'Ms.';
      const relation = (gender === 'male') ? 'Son of' : 'Daughter of';
      const pronoun = (gender === 'male') ? 'He' : 'She';
      const possessive = (gender === 'male') ? 'his' : 'her';

      if (type === 'character') {
        $('#pass_extra').hide();
        $('#midTitle').text("CHARACTER CERTIFICATE");
        $('#bodyDisp').html(`
                This is to certify that ${prefix} <span class="fill" style="min-width:280px">${student.student_name}</span> 
                ${relation} Mr. <span class="fill" style="min-width:280px">${student.guardian_name}</span> 
                was a regular student of this school during the session <span class="fill">${sess}</span>. 
                ${pronoun} has successfully completed ${possessive} studies up to class <span class="fill">${student.class_name}</span>. <br><br>
                ${possessive.charAt(0).toUpperCase() + possessive.slice(1)} date of birth according to the school record is <span class="fill">${dob}</span>. 
                During the period of ${possessive} stay in this institution, ${possessive} conduct and character remained <span class="fill">EXCELLENT</span>. 
                ${pronoun} is a hardworking and well-mannered student. <br><br>
                I wish ${gender === 'male' ? 'him' : 'her'} every success in future life.
            `);
      } else {
        $('#pass_extra').show();
        $('#midTitle').text("SCHOOL LEAVING CERTIFICATE");
        const roll = $('#roll').val() || '_________';
        const obt = $('#obt_marks').val() || '____';
        const tot = $('#tot_marks').val() || '____';

        $('#bodyDisp').html(`
                It is certified that ${prefix} <span class="fill" style="min-width:280px">${student.student_name}</span> 
                ${relation} Mr. <span class="fill" style="min-width:280px">${student.guardian_name}</span> 
                has appeared in the Secondary School Certificate (SSC) Examination 
                in the session <span class="fill">${sess}</span> under Roll No <span class="fill">${roll}</span>. <br><br>
                ${pronoun} has passed the examination by securing <span class="fill">${obt}</span> marks 
                out of <span class="fill">${tot}</span>. ${possessive.charAt(0).toUpperCase() + possessive.slice(1)} date of birth as per 
                school record is <span class="fill">${dob}</span>. <br><br>
                ${pronoun} leaves this school on ${possessive} own request after passing ${possessive} matriculation. 
                ${possessive.charAt(0).toUpperCase() + possessive.slice(1)} behavior during the period of stay has been very good.
            `);
      }
    }

    $(document).ready(function() {
      $('#certTable').DataTable();
    });
  </script>
</body>

</html>