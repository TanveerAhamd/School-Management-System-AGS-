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

// --- Active Session dhoondne ka logic ---
$active_session_id = 'all';
foreach ($sessions as $s) {
  if ($s['is_active'] == 1) {
    $active_session_id = $s['id'];
    break;
  }
}

// Agar GET mein session nahi hai, to active session use karein, warna pehla session, warna 'all'
$f_sess = $_GET['session_id'] ?? ($active_session_id !== 'all' ? $active_session_id : ($sessions[0]['id'] ?? 'all'));

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

// Filter conditions
$sess_cond = ($f_sess === 'all') ? "1=1" : "session = ?";
$f_sess_cond = ($f_sess === 'all') ? "1=1" : "session_id = ?";
$sess_param = ($f_sess === 'all') ? [] : [$f_sess];

/** 3. DATABASE QUERIES **/
// Student Stats
$st_q = $pdo->prepare("SELECT COUNT(CASE WHEN is_deleted=0 AND is_passout=0 AND is_dropout=0 THEN 1 END) as active, COUNT(CASE WHEN is_deleted=0 THEN 1 END) as total, COUNT(CASE WHEN is_passout=1 THEN 1 END) as passout, COUNT(CASE WHEN is_dropout=1 THEN 1 END) as dropout FROM students WHERE $sess_cond");
$st_q->execute($sess_param);
$st = $st_q->fetch();

// Fee Stats
$fee_q = $pdo->prepare("SELECT SUM(CASE WHEN payment_date = ? THEN amount_paid ELSE 0 END) as daily, SUM(CASE WHEN payment_date LIKE ? AND fee_type_id > 0 THEN amount_paid ELSE 0 END) as m_college, SUM(CASE WHEN payment_date LIKE ? AND fee_type_id = 0 THEN amount_paid ELSE 0 END) as m_transport FROM fee_payments WHERE $f_sess_cond");
$fee_q->execute(array_merge($sess_param, [$today, "$this_month%", "$this_month%"]));
$fees = $fee_q->fetch();

// Infrastructure
$total_staff = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$total_classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$total_sections = $pdo->query("SELECT COUNT(*) FROM sections")->fetchColumn();

// Graph: Income Trend
$inc_q = $pdo->prepare("SELECT DATE_FORMAT(payment_date, '%b') as mname, SUM(amount_paid) as total FROM fee_payments WHERE $f_sess_cond GROUP BY MONTH(payment_date) ORDER BY payment_date LIMIT 6");
$inc_q->execute($sess_param);
$trend = $inc_q->fetchAll();
$g_months = array_column($trend, 'mname');
$g_amounts = array_column($trend, 'total');

// Graph: Class Strength

$cls_q = $pdo->query("SELECT 
    c.class_name, 
    COUNT(s.id) as total_count,
    SUM(CASE WHEN s.gender = 'Male' THEN 1 ELSE 0 END) as male_count,
    SUM(CASE WHEN s.gender = 'Female' THEN 1 ELSE 0 END) as female_count
    FROM classes c 
    LEFT JOIN students s ON c.id = s.class_id AND s.is_deleted = 0 
    GROUP BY c.id")->fetchAll();

$c_labels = array_column($cls_q, 'class_name');
$c_total  = array_column($cls_q, 'total_count');
$c_male   = array_column($cls_q, 'male_count');
$c_female = array_column($cls_q, 'female_count');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Smart Dashboard | AMINA Girls School</title>
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

    .badge-white {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border-radius: 5px;
      padding: 2px 6px;
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

    @media print {
      .no-print {
        display: none !important;
      }

      #report-area {
        display: block !important;
      }
    }

    #report-area {
      display: none;
      background: #fff;
      padding: 30px;
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

          <!-- 1. MODERN WELCOME & BACKUP BAR -->
          <div class="row  no-print">
            <div class="col-12">
              <div class="card welcome-banner rounded shadow-sm">
                <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
                  <div>
                    <h4 class="mb-1"><i class="fas <?= $icon ?>"></i> <?= $greet ?>, <?= $admin_name ?>!</h4>
                    <p class="mb-0 opacity-8 text-white">Amina Girls Higher Secondary School Management Portal.</p>
                  </div>
                  <div class="mt-3 mt-md-0 d-flex flex-wrap">
                    <a href="?action=download_backup" class="btn btn-warning btn-sm mr-2 shadow-sm font-weight-bold"><i class="fas fa-database"></i> Backup DB</a>
                    <button onclick="window.print()" class="btn btn-dark btn-sm mr-2 shadow-sm font-weight-bold"><i class="fa fa-print"></i> Report</button>
                  </div>
                </div>


              </div>
            </div>
          </div>

          <!-- 2. HUB CARDS (CLUB) -->
          <div class="row no-print">
            <div class="col-xl-3 col-lg-6">
              <div class="card l-bg-purple shadow-sm">
                <div class="card-statistic-3">
                  <div class="card-icon card-icon-large"><i class="fa fa-user-graduate"></i></div>
                  <h4 class="card-title mb-3">Student Hub</h4>
                  <div class="stat-group"><span>Active Students</span> <span class="badge bg-white text-dark"><?= $st['active'] ?></span></div>
                  <!-- <div class="stat-group"><span>Registry</span> <span class="badge badge-white"><?= $st['total'] ?></span></div> -->
                  <div class="stat-group"><span>Passout/Dropout</span> <span class="badge bg-white text-dark"><?= $st['passout'] ?>/<?= $st['dropout'] ?></span></div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card l-bg-green shadow-sm">
                <div class="card-statistic-3">
                  <div class="card-icon card-icon-large"><i class="fa fa-university"></i></div>
                  <h4 class="card-title mb-3">College Fee</h4>
                  <div class="stat-group"><span>Today Cash</span> <span class="badge bg-white text-dark"><?= number_format($fees['daily']) ?></span></div>
                  <div class="stat-group"><span>This Month</span> <span class="badge bg-white text-dark"><?= number_format($fees['m_college']) ?></span></div>
                  <!-- <div class="stat-group"><span>Status</span> <span class="badge badge-white">Active</span></div> -->
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card l-bg-orange shadow-sm">
                <div class="card-statistic-3">
                  <div class="card-icon card-icon-large"><i class="fa fa-bus"></i></div>
                  <h4 class="card-title mb-3">Transport</h4>
                  <div class="stat-group"><span>Today Cash</span> <span class="badge bg-white text-dark"><?= number_format($fees['daily']) ?></span></div>
                  <div class="stat-group"><span>This Month</span> <span class="badge bg-white text-dark"><?= number_format($fees['m_transport']) ?></span></div>
                  <!-- <div class="stat-group"><span>System</span> <span class="badge badge-white">Online</span></div> -->
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
                  <!-- <div class="stat-group"><span>Ratio</span> <span class="badge badge-white">1:24</span></div> -->
                </div>
              </div>
            </div>
          </div>

          <!-- 3. QUICK LINKS -->
          <div class="row no-print">
            <div class="col-lg-8">
              <div class="row">
                <div class="col-lg-3 col-6"><a href="student-registration-form.php" class="card quick-link-card shadow-sm p-3  font-weight-bold"><i class="fa fa-user-plus d-block mb-2 font-20"></i> New Admission</a></div>
                <div class="col-lg-3 col-6"><a href="student-list.php" class="card quick-link-card shadow-sm p-3 text-info font-weight-bold"><i class="fa fa-users d-block mb-2 font-20"></i> Student List</a></div>
                <div class="col-lg-3 col-6"><a href="print_certificate.php" class="card quick-link-card shadow-sm p-3 text-dark font-weight-bold"><i class="fa fa-certificate d-block mb-2 font-20"></i> Certificates</a></div>
                <div class="col-lg-3 col-6"><a href="pay-fee.php" class="card quick-link-card shadow-sm p-3 text-success font-weight-bold"><i class="fa fa-money-bill-wave d-block mb-2 font-20"></i> Collect Fee</a></div>

              </div>
              <div class="row">
                <div class="col-lg-12 mt-4">
                  <div class="card shadow-sm">
                    <div class="card-header">
                      <h4>Class-wise Strength</h4>
                    </div>
                    <div class="card-body">
                      <div id="classChart"></div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
            <div class="col-lg-4 col-6">
              <div class="card shadow-sm">
                <div class="card-header">
                  <h4>Active Routes Analytics</h4>
                </div>
                <div class="card-body">
                  <?php
                  // LEFT JOIN use kiya hai taake saari routes show hon, chahay students 0 hon
                  // Hum ne students ki conditions (is_deleted, transport, session) ko JOIN ke 'AND' mein rakha hai
                  $routes_sql = "SELECT r.route_name, COUNT(s.id) as scount 
                     FROM transport_routes r 
                     LEFT JOIN students s ON r.id = s.route_id 
                     AND s.transport = 'Yes' 
                     AND s.is_deleted = 0 
                     AND ($sess_cond)
                     GROUP BY r.id 
                     ORDER BY r.route_name ASC";

                  $routes_stmt = $pdo->prepare($routes_sql);
                  $routes_stmt->execute($sess_param);
                  $fetch_routes = $routes_stmt->fetchAll();

                  if (empty($fetch_routes)) {
                    echo "<p class='text-center text-muted'>No routes found in system.</p>";
                  } else {
                    foreach ($fetch_routes as $r):
                      $scount = (int)$r['scount'];
                      // Percentage calculation (agar active students hon to)
                      $perc = ($st['active'] > 0) ? ($scount / $st['active']) * 100 : 0;
                  ?>
                      <div class="mb-3">
                        <div class="d-flex justify-content-between small font-weight-bold">
                          <span><?= htmlspecialchars($r['route_name']) ?></span>
                          <span><?= $scount ?> Girls</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                          <!-- Agar 0 students honge to progress bar khali nazar aayegi -->
                          <div class="progress-bar l-bg-purple" style="width: <?= $perc ?>%"></div>
                        </div>
                      </div>
                  <?php
                    endforeach;
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>



          <!-- 5. ANALYTICS GRAPHS -->
          <div class="row no-print">
            <div class="col-lg-8 col-12">
              <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h4> Recent Fee Collections</h4>
                  <a href="manage-fee-record.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover table-md">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Student Name</th>
                          <th>Invoice #</th>
                          <th>Amount Paid</th>
                          <th>Date</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        // Updated Query: Adding Reg No, Invoice, and Payment Date
                        $sql_rec_pay = "SELECT s.student_name, s.reg_no, f.amount_paid, f.fee_type_id, f.invoice_no, f.payment_date, f.id as pay_id
                                        FROM fee_payments f 
                                        JOIN students s ON f.student_id = s.id 
                                        WHERE $f_sess_cond 
                                        ORDER BY f.id DESC LIMIT 3";

                        $rec_pay = $pdo->prepare($sql_rec_pay);
                        $rec_pay->execute($sess_param);
                        $payments = $rec_pay->fetchAll();

                        if (count($payments) > 0) {
                          $sr = 1;
                          foreach ($payments as $p) {
                            // Category Badge Logic
                            if ($p['fee_type_id'] == 0) {
                              $category = '<span class="badge badge-warning" style="font-size:9px;"><i class="fa fa-bus"></i> Transport</span>';
                            } else {
                              $category = '<span class="badge badge-success" style="font-size:9px;"><i class="fa fa-university"></i> College Fee</span>';
                            }
                        ?>
                            <tr>
                              <td class="text-center"><?= $sr++ ?></td>
                              <td>
                                <div class="font-weight-bold text-uppercase small"><?= $p['student_name'] ?></div>
                                <div class="text-muted small" style="font-size: 10px;"><?= $p['reg_no'] ?></div>
                              </td>
                              <td>
                                <code class=" text-info font-weight-bold"><?= $p['invoice_no'] ?></code>
                              </td>
                              <!-- <td class="text-center">
                                <?= $category ?>
                              </td> -->
                              <td>
                                <div class="text-success font-weight-bold">Rs. <?= number_format($p['amount_paid']) ?></div>
                              </td>
                              <td class="small">
                                <?= date('d-M-y', strtotime($p['payment_date'])) ?>
                                <!-- <div class="text-muted" style="font-size: 10px;"><?= date('h:i A', strtotime($p['payment_date'])) ?></div> -->
                              </td>
                              <!-- <td class="text-center">
                                <a href="print-receipt.php?id=<?= $p['pay_id'] ?>" class="btn btn-outline-dark btn-sm" data-toggle="tooltip" title="Print Receipt">
                                  <i class="fas fa-print"></i>
                                </a>
                              </td> -->
                            </tr>
                        <?php
                          }
                        } else {
                          echo "<tr><td colspan='7' class='text-center'>No transactions found in this session.</td></tr>";
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <!-- Student Analytics Chart Card -->
            <div class="col-xl-4 col-lg-6 col-md-12 col-12">
              <div class="card shadow-sm" style="min-height: 280px;">
                <div class="card-header">
                  <h4><i class="fas fa-chart-pie text-success"></i> Student Analytics</h4>
                </div>
                <div class="card-body ">
                  <div class="row align-items-center">
                    <!-- Left Side: Custom Vertical Legends -->
                    <div class="col-5">
                      <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                          <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                              <span class="mr-2" style="height: 10px; width: 10px; background-color: #6777ef; border-radius: 50%; display: inline-block;"></span>
                              <span class="font-weight-bold small text-muted">Active</span>
                            </div>
                            <span class="badge badge-light small"><?= (int)$st['active'] ?></span>
                          </div>
                        </li>
                        <li class="mb-3">
                          <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                              <span class="mr-2" style="height: 10px; width: 10px; background-color: #28a745; border-radius: 50%; display: inline-block;"></span>
                              <span class="font-weight-bold small text-muted">Passout</span>
                            </div>
                            <span class="badge badge-light small"><?= (int)$st['passout'] ?></span>
                          </div>
                        </li>
                        <li class="mb-0">
                          <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                              <span class="mr-2" style="height: 10px; width: 10px; background-color: #dc3545; border-radius: 50%; display: inline-block;"></span>
                              <span class="font-weight-bold small text-muted">Dropout</span>
                            </div>
                            <span class="badge badge-light small"><?= (int)$st['dropout'] ?></span>
                          </div>
                        </li>
                      </ul>

                      <!-- Optional: Total Text below labels -->

                    </div>

                    <!-- Right Side: The Donut Chart -->
                    <div class="col-7 p-0 text-center">
                      <div id="studentStatusChart" style="min-height: 180px;"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>



          <!-- 6. DATA TABLES -->
          <div class="row no-print">
            <div class="col-lg-12">
              <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h4> Recent Students Admission</h4>
                  <a href="student-list.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th class="text-center">#</th>
                          <th>Reg#</th>
                          <th>Student Name</th>
                          <th>Father Name</th>
                          <th>Class (Section)</th>
                          <th>Date</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        // Updated Query to fetch Father Name, Class and Section names
                        $rec_st = $pdo->prepare("SELECT s.id, s.student_name, s.guardian_name, s.reg_no, s.admission_date, s.student_photo, 
                                                 c.class_name, sec.section_name 
                                                 FROM students s
                                                 LEFT JOIN classes c ON s.class_id = c.id
                                                 LEFT JOIN sections sec ON s.section_id = sec.id
                                                 WHERE $sess_cond 
                                                 ORDER BY s.id DESC LIMIT 5");
                        $rec_st->execute($sess_param);
                        $admissions = $rec_st->fetchAll();

                        if (count($admissions) > 0) {
                          $sr = 1; // Serial Number Counter
                          foreach ($admissions as $s) {
                            $photo = (!empty($s['student_photo']) && file_exists($s['student_photo'])) ? $s['student_photo'] : 'assets/img/userdummypic.png';
                        ?>
                            <tr>
                              <td class="text-center"><?= $sr++ ?></td>
                              <td>
                                <div class="badge badge-light border font-weight-bold"><?= $s['reg_no'] ?></div>
                              </td>
                              <td>
                                <img alt="image" src="<?= $photo ?>" class="rounded-circle mr-2" width="30" height="30" style="object-fit: cover; border: 1px solid #eee;">
                                <span class="font-weight-bold text-uppercase small"><?= $s['student_name'] ?></span>
                              </td>
                              <td class="text-uppercase small"><?= $s['guardian_name'] ?></td>
                              <td>
                                <span class="badge badge-primary"><?= $s['class_name'] ?></span>
                                <span class="badge badge-info"><?= $s['section_name'] ?></span>
                              </td>
                              <td class="small"><?= date('d-M-Y', strtotime($s['admission_date'])) ?></td>
                              <td>
                                <div class="d-flex">
                                  <a href="student-edit-page.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm mr-1" data-toggle="tooltip" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                  </a>
                                  <a href="student-detail-page.php?id=<?= $s['id'] ?>" class="btn btn-info btn-sm" data-toggle="tooltip" title="View Detail">
                                    <i class="fas fa-eye"></i>
                                  </a>
                                </div>
                              </td>
                            </tr>
                        <?php
                          }
                        } else {
                          echo "<tr><td colspan='7' class='text-center'>No recent admissions found</td></tr>";
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </section>
      </div>

      <!-- REPORT AREA (HIDDEN) -->
      <div id="report-area">
        <center>
          <h1>AMINA GIRLS HIGHER SECONDARY SCHOOL & COLLEGE</h1>
          <p>SMS Session Summary Report</p>
          <hr>
        </center>
        <h4>Financial Summary:</h4>
        <table style="width:100%; border-collapse:collapse;" border="1">
          <tr>
            <th>Description</th>
            <th>Daily</th>
            <th>Monthly</th>
          </tr>
          <tr>
            <td>College Fees</td>
            <td><?= number_format($fees['daily']) ?></td>
            <td><?= number_format($fees['m_college']) ?></td>
          </tr>
          <tr>
            <td>Transport Fees</td>
            <td><?= number_format($fees['daily']) ?></td>
            <td><?= number_format($fees['m_transport']) ?></td>
          </tr>
        </table>
      </div>

      <?php include 'include/footer.php'; ?>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/apexcharts/apexcharts.min.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script>
    new ApexCharts(document.querySelector("#incomeChart"), {
      series: [{
        name: 'Income',
        data: <?= json_encode($g_amounts) ?>
      }],
      chart: {
        type: 'area',
        height: 280,
        toolbar: {
          show: false
        }
      },
      colors: ['#6777ef'],
      xaxis: {
        categories: <?= json_encode($g_months) ?>
      }
    }).render();

    var options = {
      series: [<?= (int)$st['active'] ?>, <?= (int)$st['passout'] ?>, <?= (int)$st['dropout'] ?>],
      chart: {
        type: 'donut',
        height: 200, // Thora bara height behtar lagta hy
        toolbar: {
          show: false
        }
      },
      labels: ['Active', 'Passout', 'Dropout'],
      colors: ['#6777ef', '#28a745', '#dc3545'], // Blue, Green, Red
      dataLabels: {
        enabled: false // Chote chart me labels band rkhna behtar hy
      },
      legend: {
        show: false // Humne neeche custom legend bana li hy isliye yahan band kr di
      },
      plotOptions: {
        pie: {
          donut: {
            size: '70%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Total',
                formatter: function(w) {
                  return <?= (int)$st['active'] + (int)$st['passout'] + (int)$st['dropout'] ?>
                }
              }
            }
          }
        }
      },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 200
          }
        }
      }]
    };

    var chart = new ApexCharts(document.querySelector("#studentStatusChart"), options);
    chart.render();



    var options = {
      series: [{
        name: 'Total Students',
        data: <?= json_encode($c_total) ?>
      }, {
        name: 'Male',
        data: <?= json_encode($c_male) ?>
      }, {
        name: 'Female',
        data: <?= json_encode($c_female) ?>
      }],
      chart: {
        type: 'bar',
        height: 300,
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '55%',
          borderRadius: 5
        },
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      xaxis: {
        categories: <?= json_encode($c_labels) ?>,
      },
      fill: {
        opacity: 1
      },
      // Colors: Total (Blue), Male (Deep Blue), Female (Pink)
      colors: ['#3abaf4', '#007bff', '#e83e8c'],
      legend: {
        position: 'top',
        horizontalAlign: 'center',
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return val + " Students"
          }
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#classChart"), options);
    chart.render();
  </script>
</body>

</html>