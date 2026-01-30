<?php
require_once 'auth.php';

/**
 * 1. DATABASE BACKUP LOGIC
 */
if (isset($_GET['action']) && $_GET['action'] == 'download_backup') {
  $tables = array();
  $result = $pdo->query("SHOW TABLES");
  while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
  }
  $return = "-- AGS DATABASE BACKUP\n-- Date: " . date('d-M-Y h:i A') . "\n\n";
  foreach ($tables as $table) {
    $result = $pdo->query("SELECT * FROM $table");
    $num_fields = $result->columnCount();
    $return .= "DROP TABLE IF EXISTS $table;";
    $row2 = $pdo->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM);
    $return .= "\n\n" . $row2[1] . ";\n\n";
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
      $return .= "INSERT INTO $table VALUES(";
      for ($j = 0; $j < $num_fields; $j++) {
        $row[$j] = addslashes($row[$j]);
        $return .= isset($row[$j]) ? '"' . $row[$j] . '"' : '""';
        if ($j < ($num_fields - 1)) {
          $return .= ',';
        }
      }
      $return .= ");\n";
    }
    $return .= "\n\n\n";
  }
  header('Content-Type: application/octet-stream');
  header("Content-disposition: attachment; filename=\"AGS_Backup_" . date('Y-m-d') . ".sql\"");
  echo $return;
  exit;
}

// 2. Session & Date Handling
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC")->fetchAll();
$active_session_id = 'all';
$active_session_name = 'All Sessions';
foreach ($sessions as $s) {
  if ($s['is_active'] == 1) {
    $active_session_id = $s['id'];
    $active_session_name = $s['session_name'];
    break;
  }
}
$f_sess = $_GET['session_id'] ?? ($active_session_id !== 'all' ? $active_session_id : ($sessions[0]['id'] ?? 'all'));
$current_session_name = $active_session_name;
if ($f_sess !== 'all') {
  foreach ($sessions as $s) {
    if ($s['id'] == $f_sess) {
      $current_session_name = $s['session_name'];
      break;
    }
  }
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$hour = date('H');
if ($hour < 12) {
  $greet = "Good Morning";
  $icon = "fa-sun";
} elseif ($hour < 17) {
  $greet = "Good Afternoon";
  $icon = "fa-cloud-sun";
} else {
  $greet = "Good Evening";
  $icon = "fa-moon";
}

$today = date('Y-m-d');
$this_month = date('Y-m');

$sess_cond = ($f_sess === 'all') ? "1=1" : "session = ?";
$f_sess_cond = ($f_sess === 'all') ? "1=1" : "session_id = ?";
$sess_param = ($f_sess === 'all') ? [] : [$f_sess];

/** 3. DATABASE QUERIES **/
// Student Overall Stats
$st_q = $pdo->prepare("SELECT COUNT(CASE WHEN is_deleted=0 AND is_passout=0 AND is_dropout=0 THEN 1 END) as active, COUNT(CASE WHEN is_deleted=0 THEN 1 END) as total, COUNT(CASE WHEN is_passout=1 THEN 1 END) as passout, COUNT(CASE WHEN is_dropout=1 THEN 1 END) as dropout FROM students WHERE $sess_cond");
$st_q->execute($sess_param);
$st = $st_q->fetch();

// Financial Stats
$fee_sql = "SELECT 
    SUM(CASE WHEN payment_date = CURDATE() AND fee_type_id > 0 THEN amount_paid ELSE 0 END) as d_college, 
    SUM(CASE WHEN payment_date = CURDATE() AND fee_type_id = 0 THEN amount_paid ELSE 0 END) as d_transport, 
    SUM(CASE WHEN payment_date LIKE '$this_month%' AND fee_type_id > 0 THEN amount_paid ELSE 0 END) as m_college, 
    SUM(CASE WHEN payment_date LIKE '$this_month%' AND fee_type_id = 0 THEN amount_paid ELSE 0 END) as m_transport,
    SUM(CASE WHEN fee_type_id > 0 THEN amount_paid ELSE 0 END) as total_college_session,
    SUM(CASE WHEN fee_type_id = 0 THEN amount_paid ELSE 0 END) as total_transport_session
    FROM fee_payments WHERE $f_sess_cond";
$fee_q = $pdo->prepare($fee_sql);
$fee_q->execute($sess_param);
$fees = $fee_q->fetch();

$total_staff = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$total_classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$total_sections = $pdo->query("SELECT COUNT(*) FROM sections")->fetchColumn();

// Class-wise Detailed Stats for Graph & Report
$cls_sql = "SELECT c.class_name, COUNT(s.id) as total_adm,
    SUM(CASE WHEN s.is_deleted = 0 AND s.is_passout = 0 AND s.is_dropout = 0 THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN s.is_passout = 1 THEN 1 ELSE 0 END) as passout_count,
    SUM(CASE WHEN s.is_dropout = 1 THEN 1 ELSE 0 END) as dropout_count,
    SUM(CASE WHEN (s.gender = 'Male' OR s.gender = 'MALE') AND s.is_deleted = 0 AND s.is_passout = 0 AND s.is_dropout = 0 THEN 1 ELSE 0 END) as active_male,
    SUM(CASE WHEN (s.gender = 'Female' OR s.gender = 'FEMALE') AND s.is_deleted = 0 AND s.is_passout = 0 AND s.is_dropout = 0 THEN 1 ELSE 0 END) as active_female
    FROM classes c LEFT JOIN students s ON c.id = s.class_id AND ($sess_cond)
    GROUP BY c.id ORDER BY c.id ASC";
$cls_q_stmt = $pdo->prepare($cls_sql);
$cls_q_stmt->execute($sess_param);
$cls_data = $cls_q_stmt->fetchAll();

$c_labels = array_column($cls_data, 'class_name');
$c_total  = array_column($cls_data, 'active_count');
$c_male   = array_column($cls_data, 'active_male');
$c_female = array_column($cls_data, 'active_female');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Smart Dashboard | AGS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <style>
    .welcome-banner {
      background: linear-gradient(to right, #6777ef, #c7cbeaff);
      color: #fff;
    }

    .card-statistic-3 {
      padding: 22px !important;
      border-radius: 12px !important;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .stat-group {
      border-bottom: 1px solid rgba(255, 255, 255, 0.15);
      padding: 5px 0;
      display: flex;
      justify-content: space-between;
      font-size: 13px;
    }

    .quick-link-card {
      transition: 0.3s;
      border: none;
      border-radius: 12px;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      text-decoration: none !important;
    }

    .quick-link-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }

    /* REPORT STYLES */
    #printable-report {
      display: none;
      font-family: 'Times New Roman', serif !important;
    }

    @media print {
      body * {
        visibility: hidden;
      }

      .no-print,
      .navbar,
      .main-sidebar,
      .main-footer,
      .btn,
      .loader {
        display: none !important;
      }

      #printable-report,
      #printable-report * {
        visibility: visible;
      }

      #printable-report {
        display: block !important;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 30px;
        background: #fff;
        color: #000;
      }

      .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.08;
        z-index: -1;
        width: 550px;
        pointer-events: none;
      }

      .report-header {
        text-align: center;
        border-bottom: 3px solid #000;
        padding-bottom: 15px;
        font-size: 16px;
        margin-bottom: 30px;
      }

      .report-box {
        border: 2px solid #000;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
      }

      .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
      }

      .report-table th,
      .report-table td {
        border: 1.5px solid #000;
        padding: 10px;
        text-align: center;
        font-size: 18px;
      }

      .report-table th {
        background: #f0f0f0 !important;
        font-weight: bold;
      }

      .report-footer-credit {
        position: fixed;
        bottom: 10px;
        width: 100%;
        text-align: center;
        font-size: 12px;
        border-top: 1px solid #ddd;
        padding-top: 5px;
      }
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
          <!-- WELCOME BAR -->
          <div class="row no-print">
            <div class="col-12">
              <div class="card welcome-banner rounded shadow-sm">
                <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
                  <div>
                    <h4 class="mb-1"><i class="fas <?= $icon ?>"></i> <?= $greet ?>, <?= $admin_name ?>!</h4>
                    <p class="mb-0 text-white">Welcome to Amina Girls High School Student Management System | Dashbaord.</p>
                  </div>
                  <div class="mt-3 mt-md-0 d-flex flex-wrap">
                    <a href="?action=download_backup" class="btn btn-warning btn-sm mr-2 shadow-sm font-weight-bold"><i class="fas fa-database"></i> Backup DB</a>
                    <button onclick="window.print()" class="btn btn-dark btn-sm shadow-sm font-weight-bold"><i class="fa fa-print"></i> Print Report</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- HUB CARDS -->
          <div class="row no-print">
            <div class="col-xl-3 col-lg-6">
              <div class="card l-bg-purple shadow-sm">
                <div class="card-statistic-3">
                  <div class="card-icon card-icon-large"><i class="fa fa-user-graduate"></i></div>
                  <h4 class="card-title mb-3">Student Hub</h4>
                  <div class="stat-group"><span>Active Students</span> <span class="badge bg-white text-dark"><?= $st['active'] ?></span></div>
                  <div class="stat-group"><span>Passout/Dropout</span> <span class="badge bg-white text-dark"><?= $st['passout'] ?>/<?= $st['dropout'] ?></span></div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card l-bg-green shadow-sm">
                <div class="card-statistic-3">
                  <div class="card-icon card-icon-large"><i class="fa fa-university"></i></div>
                  <h4 class="card-title mb-3">College Fee</h4>
                  <div class="stat-group"><span>Today Cash</span><span class="badge bg-white text-dark">Rs. <?= number_format($fees['d_college']) ?></span></div>
                  <div class="stat-group"><span>This Month</span><span class="badge bg-white text-dark">Rs. <?= number_format($fees['m_college']) ?></span></div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card l-bg-orange shadow-sm">
                <div class="card-statistic-3">
                  <div class="card-icon card-icon-large"><i class="fa fa-bus"></i></div>
                  <h4 class="card-title mb-3">Transport</h4>
                  <div class="stat-group"><span>Today Cash</span><span class="badge bg-white text-dark">Rs. <?= number_format($fees['d_transport']) ?></span></div>
                  <div class="stat-group"><span>This Month</span><span class="badge bg-white text-dark">Rs. <?= number_format($fees['m_transport']) ?></span></div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card l-bg-cyan shadow-sm">
                <div class="card-statistic-3">
                  <div class="card-icon card-icon-large"><i class="fa fa-building"></i></div>
                  <h4 class="card-title mb-3">Institution</h4>
                  <div class="stat-group"><span>Teacher</span> <span class="badge bg-white text-dark"><?= $total_staff ?></span></div>
                  <div class="stat-group"><span>Classes/Sections</span> <span class="badge bg-white text-dark"><?= $total_classes ?>/<?= $total_sections ?></span></div>
                </div>
              </div>
            </div>
          </div>

          <!-- QUICK LINKS & CLASS GRAPH -->
          <div class="row no-print">
            <div class="col-lg-8">
              <div class="row">
                <div class="col-lg-3 col-6 mb-2"><a href="student-registration-form.php" class="card quick-link-card shadow-sm p-3 font-weight-bold"><i class="fa fa-user-plus d-block mb-2 font-20"></i> New Admission</a></div>
                <div class="col-lg-3 col-6 mb-2"><a href="student-list.php" class="card quick-link-card shadow-sm p-3 text-info font-weight-bold"><i class="fa fa-users d-block mb-2 font-20"></i> Student List</a></div>
                <div class="col-lg-3 col-6 mb-2"><a href="print_certificate.php" class="card quick-link-card shadow-sm p-3 text-dark font-weight-bold"><i class="fa fa-certificate d-block mb-2 font-20"></i> Certificates</a></div>
                <div class="col-lg-3 col-6 mb-2"><a href="pay-fee.php" class="card quick-link-card shadow-sm p-3 text-success font-weight-bold"><i class="fa fa-money-bill-wave d-block mb-2 font-20"></i> Collect Fee</a></div>
              </div>
              <div class="card shadow-sm mt-4">
                <div class="card-header">
                  <h4>Class-wise Analytics (Active Male/Female)</h4>
                </div>
                <div class="card-body">
                  <div id="classChart"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-12">
              <div class="card shadow-sm">
                <div class="card-header">
                  <h4>Active Routes Analytics</h4>
                </div>
                <div class="card-body">
                  <?php
                  $routes_sql = "SELECT r.route_name, COUNT(s.id) as scount FROM transport_routes r LEFT JOIN students s ON r.id = s.route_id AND s.transport = 'Yes' AND s.is_deleted = 0 AND ($sess_cond) GROUP BY r.id ORDER BY r.route_name ASC";
                  $routes_stmt = $pdo->prepare($routes_sql);
                  $routes_stmt->execute($sess_param);
                  foreach ($routes_stmt->fetchAll() as $r):
                    $perc = ($st['active'] > 0) ? ($r['scount'] / $st['active']) * 100 : 0; ?>
                    <div class="mb-3">
                      <div class="d-flex justify-content-between small font-weight-bold"><span><?= $r['route_name'] ?></span><span><?= $r['scount'] ?></span></div>
                      <div class="progress" style="height: 5px;">
                        <div class="progress-bar l-bg-purple" style="width: <?= $perc ?>%"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- RECENT FEES & STUDENT ANALYTICS (RESTORED) -->
          <div class="row no-print">
            <div class="col-lg-8 col-12">
              <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h4>Recent Fee Collections</h4><a href="manage-fee-record.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover table-md">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Student</th>
                          <th>Invoice</th>
                          <th>Amount Paid</th>
                          <th>Date</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $sql_rec_pay = "SELECT s.student_name, s.reg_no, f.amount_paid, f.invoice_no, f.payment_date FROM fee_payments f JOIN students s ON f.student_id = s.id WHERE $f_sess_cond ORDER BY f.id DESC LIMIT 3";
                        $rec_pay = $pdo->prepare($sql_rec_pay);
                        $rec_pay->execute($sess_param);
                        $sr = 1;
                        foreach ($rec_pay->fetchAll() as $p) { ?>
                          <tr>
                            <td><?= $sr++ ?></td>
                            <td>
                              <div class="font-weight-bold small"><?= $p['student_name'] ?></div><small><?= $p['reg_no'] ?></small>
                            </td>
                            <td><code class="text-info"><?= $p['invoice_no'] ?></code></td>
                            <td>
                              <div class="text-success font-weight-bold">Rs. <?= number_format($p['amount_paid']) ?></div>
                            </td>
                            <td class="small"><?= date('d-M-y', strtotime($p['payment_date'])) ?></td>
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-4 col-lg-4  col-12">
              <div class="card shadow-sm" style="min-height: 280px;">
                <div class="card-header">
                  <h4>Student Analytics</h4>
                </div>
                <div class="card-body">
                  <div id="studentStatusChart"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- RECENT ADMISSIONS WITH VIEW DETAIL -->
          <div class="row no-print">
            <div class="col-12">
              <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h4>Recent 5 Admissions</h4><a href="student-list.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Reg#</th>
                          <th>Photo</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Class (Section)</th>
                          <th>Date</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $rec_st = $pdo->prepare("SELECT s.id, s.student_name, s.guardian_name, s.reg_no, s.admission_date, s.student_photo, c.class_name, sec.section_name FROM students s LEFT JOIN classes c ON s.class_id = c.id LEFT JOIN sections sec ON s.section_id = sec.id WHERE $sess_cond ORDER BY s.id DESC LIMIT 5");
                        $rec_st->execute($sess_param);
                        foreach ($rec_st->fetchAll() as $s) {
                          $photo = (!empty($s['student_photo']) && file_exists($s['student_photo'])) ? $s['student_photo'] : 'assets/img/userdummypic.png'; ?>
                          <tr>
                            <td><span class="badge badge-light border"><?= $s['reg_no'] ?></span></td>
                            <td><img src="<?= $photo ?>" class="rounded-circle" width="35" height="35" style="object-fit: cover;"></td>
                            <td><span class="font-weight-bold text-uppercase small"><?= $s['student_name'] ?></span></td>
                            <td><?= $s['guardian_name'] ?></td>
                            <td><span class="badge badge-primary"><?= $s['class_name'] ?></span> <span class="badge badge-info"><?= $s['section_name'] ?></span></td>
                            <td class="small"><?= date('d-M-Y', strtotime($s['admission_date'])) ?></td>
                            <td><a href="student-detail-page.php?id=<?= $s['id'] ?>" class="btn btn-outline-info btn-sm"><i class="fa fa-eye"></i> View Detail</a></td>
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- ============================================== -->
      <!-- OFFICIAL PERFORMANCE REPORT (FULL STATS) -->
      <!-- ============================================== -->
      <div id="printable-report">
        <div class="report-container">
          <img src="<?= $sch_logo ?>" class="watermark">
          <div class="report-header">
            <img src="<?= $sch_logo ?>" style="height: 90px; margin-bottom: 5px;">
            <h1 class="text-center text-nowrap m-0 font-weight-bold" style="letter-spacing: 2px;">
              <?= htmlspecialchars($sch_name) ?>
            </h1>
            <p style="margin: 0; font-size: 14px; letter-spacing: 1px;">Gailywal 21-MPR Lodhran | OFFICIAL REPORT</p>
            <div style="margin-top: 15px; border: 3px solid #000; display: inline-block; padding: 6px 40px; font-weight: bold; background: #f8f9fa; font-size: 18px;">SESSION: <?= htmlspecialchars($current_session_name) ?></div>
            <p style="font-size: 12px; margin-top: 10px;">Generated On: <?= date('d-M-Y h:i A') ?> | System Admin: <?= $admin_name ?></p>
          </div>

          <div class="row" style="display: flex; gap: 20px;">
            <div class="col-4" style="flex: 1;">
              <div class="report-box">
                <h6>Enrollment Status</h6>
                <table style="width: 100%; font-size: 18px;">
                  <tr>
                    <td>Total Admissions:</td>
                    <td style="text-align:right;"><strong><?= $st['total'] ?></strong></td>
                  </tr>
                  <tr>
                    <td>Active Students:</td>
                    <td style="text-align:right;"><strong><?= $st['active'] ?></strong></td>
                  </tr>
                  <tr>
                    <td>Session Passout:</td>
                    <td style="text-align:right;"><strong><?= $st['passout'] ?></strong></td>
                  </tr>
                  <tr>
                    <td>Session Dropout:</td>
                    <td style="text-align:right;"><strong><?= $st['dropout'] ?></strong></td>
                  </tr>
                </table>
              </div>
            </div>
            <div class="col-4" style="flex: 1;">
              <div class="report-box">
                <h6>Finance Statistics</h6>
                <table style="width: 100%; font-size: 18px;">
                  <tr>
                    <td>College Fee:</td>
                    <td style="text-align:right;">Rs. <?= number_format($fees['total_college_session']) ?></td>
                  </tr>
                  <tr>
                    <td>Transport Fee:</td>
                    <td style="text-align:right;">Rs. <?= number_format($fees['total_transport_session']) ?></td>
                  </tr>
                  <tr style="border-top: 2px solid #000;">
                    <td><strong>Net Total:</strong></td>
                    <td style="text-align:right;"><strong>Rs. <?= number_format($fees['total_college_session'] + $fees['total_transport_session']) ?></strong></td>
                  </tr>
                </table>
              </div>
            </div>
            <div class="col-4" style="flex: 1;">
              <div class="report-box">
                <h6>Institutional Assets</h6>
                <table style="width: 100%; font-size: 18px;">
                  <tr>
                    <td>Faculty Members:</td>
                    <td style="text-align:right;"><strong><?= $total_staff ?></strong></td>
                  </tr>
                  <tr>
                    <td>Total Classes:</td>
                    <td style="text-align:right;"><strong><?= $total_classes ?></strong></td>
                  </tr>
                  <tr>
                    <td>Sections:</td>
                    <td style="text-align:right;"><strong><?= $total_sections ?></strong></td>
                  </tr>
                </table>
              </div>
            </div>
          </div>

          <h3 style="margin-top: 25px; font-size: 18px; border-bottom: 2px solid #000; display: inline-block;">CLASS-WISE ANALYTICS LEDGER</h3>
          <table class="report-table">
            <thead>
              <tr>
                <th>S#</th>
                <th style="text-align:left;">Class Name</th>
                <th>Total Adm.</th>
                <th>Passout</th>
                <th>Dropout</th>
                <th>Male(Active)</th>
                <th>Female(Active)</th>
                <th style="background:#eee !important;">Net Active</th>
              </tr>
            </thead>
            <tbody>
              <?php $sr = 1;
              $g_adm = 0;
              $g_pass = 0;
              $g_drop = 0;
              $g_am = 0;
              $g_af = 0;
              $g_net = 0;
              foreach ($cls_data as $cls) {
                $g_adm += $cls['total_adm'];
                $g_pass += $cls['passout_count'];
                $g_drop += $cls['dropout_count'];
                $g_am += $cls['active_male'];
                $g_af += $cls['active_female'];
                $g_net += $cls['active_count']; ?>
                <tr>
                  <td><?= $sr++ ?></td>
                  <td style="text-align:left; font-weight: bold;"><?= $cls['class_name'] ?></td>
                  <td><?= $cls['total_adm'] ?></td>
                  <td><?= $cls['passout_count'] ?></td>
                  <td><?= $cls['dropout_count'] ?></td>
                  <td><?= $cls['active_male'] ?></td>
                  <td><?= $cls['active_female'] ?></td>
                  <td><strong><?= $cls['active_count'] ?></strong></td>
                </tr>
              <?php } ?>
              <tr style="font-weight: bold; background: #ddd !important;">
                <td colspan="2" style="text-align:right;">CONSOLIDATED TOTAL:</td>
                <td><?= $g_adm ?></td>
                <td><?= $g_pass ?></td>
                <td><?= $g_drop ?></td>
                <td><?= $g_am ?></td>
                <td><?= $g_af ?></td>
                <td><?= $g_net ?></td>
              </tr>
            </tbody>
          </table>

          <div style="margin-top: 80px; display: flex; justify-content: space-between;">
            <div style="text-align: center; width: 200px; border-top: 2px solid #000; padding-top: 5px; font-weight: bold;">Accountant Sig.</div>
            <div style="text-align: center; width: 200px; border-top: 2px solid #000; padding-top: 5px; font-weight: bold;">Admin Officer</div>
            <div style="text-align: center; width: 200px; border-top: 2px solid #000; padding-top: 5px; font-weight: bold;">Principal Sig.</div>
          </div>

          <div class="report-footer-credit">
            Student Management System | Design and develop by (AGHS_TECH) <strong>Tanveer Ahmad</strong>
          </div>
        </div>
      </div>

      <footer class="main-footer no-print">
        <div class="footer-left">Student Management System | Design and develop by <a href="#">Tanveer Ahmad</a></div>
      </footer>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/apexcharts/apexcharts.min.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script>
    new ApexCharts(document.querySelector("#classChart"), {
      series: [{
          name: 'Total Active',
          data: <?= json_encode($c_total) ?>
        },
        {
          name: 'Male',
          data: <?= json_encode($c_male) ?>
        },
        {
          name: 'Female',
          data: <?= json_encode($c_female) ?>
        }
      ],
      chart: {
        type: 'bar',
        height: 330,
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          columnWidth: '55%',
          borderRadius: 5
        }
      },
      colors: ['#3abaf4', '#007bff', '#e83e8c'],
      xaxis: {
        categories: <?= json_encode($c_labels) ?>
      },
      legend: {
        position: 'top'
      }
    }).render();

    new ApexCharts(document.querySelector("#studentStatusChart"), {
      series: [<?= (int)$st['active'] ?>, <?= (int)$st['passout'] ?>, <?= (int)$st['dropout'] ?>],
      chart: {
        type: 'donut',
        height: 250
      },
      labels: ['Active', 'Passout', 'Dropout'],
      colors: ['#6777ef', '#28a745', '#dc3545'],
      legend: {
        position: 'bottom',
        formatter: function(val, opts) {
          // لیجنڈ میں نام کے ساتھ نمبر دکھانے کے لیے
          return val + ": " + opts.w.globals.series[opts.seriesIndex];
        }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '65%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Total',
                color: '#444',
                fontSize: '16px',
                fontWeight: 600,
                formatter: function(w) {
                  // درمیان میں کل تعداد دکھانے کے لیے
                  return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                }
              }
            }
          }
        }
      },
      dataLabels: {
        enabled: true,
        formatter: function(val, opts) {
          // سلائس کے اوپر فیصد کے بجائے اصل نمبر دکھانے کے لیے
          return opts.w.config.series[opts.seriesIndex];
        },
        style: {
          fontSize: '12px',
          colors: ['#fff']
        }
      }
    }).render();
  </script>
</body>

</html>