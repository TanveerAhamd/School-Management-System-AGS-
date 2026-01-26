<?php

/**
 * 1. DATABASE CONNECTION & AJAX HANDLERS
 */
require_once 'auth.php'; // Ensure this file has your $pdo connection

if (isset($_GET['action'])) {
  header('Content-Type: application/json');

  // Action: Fetch Sections for Filters
  if ($_GET['action'] == 'fetch_sections') {
    $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
    $stmt->execute([$_GET['class_id'] ?? 0]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
  }

  // ACTION: FETCH COMPLETE STUDENT LEDGER (Assigned vs Paid)
  if ($_GET['action'] == 'get_student_ledger' && isset($_GET['student_id'])) {
    $st_id = $_GET['student_id'];

    $stmt_info = $pdo->prepare("SELECT s.*, c.class_name, sec.section_name 
                                    FROM students s 
                                    LEFT JOIN classes c ON s.class_id = c.id 
                                    LEFT JOIN sections sec ON s.section_id = sec.id 
                                    WHERE s.id = ?");
    $stmt_info->execute([$st_id]);
    $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    // Assigned Fees with Paid Status check for Ticks (✓)
    $stmt_assigned = $pdo->prepare("SELECT id, fee_title, amount FROM fee_types WHERE class_id = ?");
    $stmt_assigned->execute([$info['class_id']]);
    $assigned_raw = $stmt_assigned->fetchAll(PDO::FETCH_ASSOC);

    $assigned_fees = [];
    foreach ($assigned_raw as $f) {
      $stmt_check = $pdo->prepare("SELECT SUM(amount_paid + special_discount) FROM fee_payments WHERE student_id = ? AND fee_type_id = ?");
      $stmt_check->execute([$st_id, $f['id']]);
      $paid_val = (float)$stmt_check->fetchColumn();
      $f['is_paid'] = ($paid_val >= (float)$f['amount']);
      $assigned_fees[] = $f;
    }

    $transport_fare = 0;
    $tr_paid_status = false;
    if ($info['transport'] == 'Yes' && !empty($info['route_id'])) {
      $stmt_tr = $pdo->prepare("SELECT fare FROM transport_allocations WHERE route_id = ? LIMIT 1");
      $stmt_tr->execute([$info['route_id']]);
      $transport_fare = (float)$stmt_tr->fetchColumn();

      $stmt_tr_check = $pdo->prepare("SELECT SUM(amount_paid + special_discount) FROM fee_payments WHERE student_id = ? AND fee_type_id = 0");
      $stmt_tr_check->execute([$st_id]);
      $tr_paid_val = (float)$stmt_tr_check->fetchColumn();
      $tr_paid_status = ($tr_paid_val >= $transport_fare);
    }

    $stmt_pay = $pdo->prepare("SELECT fp.*, COALESCE(ft.fee_title, 'Transport Fee') as fee_name 
                                    FROM fee_payments fp 
                                    LEFT JOIN fee_types ft ON fp.fee_type_id = ft.id 
                                    WHERE fp.student_id = ? ORDER BY fp.payment_date ASC");
    $stmt_pay->execute([$st_id]);
    $history = $stmt_pay->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
      'info' => $info,
      'assigned' => $assigned_fees,
      'transport_fare' => $transport_fare,
      'tr_paid' => $tr_paid_status,
      'history' => $history
    ]);
    exit;
  }
}

/**
 * 2. GLOBAL FILTER LOGIC
 */
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC")->fetchAll();

$f_sess   = $_GET['session_id'] ?? '';
$f_class  = $_GET['class_id'] ?? '';
$f_sec    = $_GET['section_id'] ?? '';
$f_s_stat = $_GET['student_status'] ?? 'All';
$f_p_stat = $_GET['payment_status'] ?? 'All';

$query = "SELECT * FROM (
    SELECT s.id, s.reg_no, s.student_name, s.guardian_name, s.class_id, s.section_id, s.transport, s.route_id, s.session,
           s.is_deleted, s.is_passout, s.is_dropout,
           c.class_name, sec.section_name,
           ( (SELECT IFNULL(SUM(amount), 0) FROM fee_types WHERE class_id = s.class_id) + 
             (CASE WHEN s.transport = 'Yes' THEN IFNULL((SELECT fare FROM transport_allocations WHERE route_id = s.route_id LIMIT 1), 0) ELSE 0 END) 
           ) as total_payable,
           IFNULL((SELECT SUM(amount_paid + special_discount) FROM fee_payments WHERE student_id = s.id), 0) as total_cleared
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN sections sec ON s.section_id = sec.id
) as ledger WHERE 1=1";

$params = [];
if (!empty($f_sess)) {
  $query .= " AND session = ?";
  $params[] = $f_sess;
}
if (!empty($f_class)) {
  $query .= " AND class_id = ?";
  $params[] = $f_class;
}
if (!empty($f_sec)) {
  $query .= " AND section_id = ?";
  $params[] = $f_sec;
}

if ($f_s_stat == 'Active') {
  $query .= " AND is_deleted = 0 AND is_passout = 0 AND is_dropout = 0";
} elseif ($f_s_stat == 'Dropout') {
  $query .= " AND is_dropout = 1";
} elseif ($f_s_stat == 'Passout') {
  $query .= " AND is_passout = 1";
}

if ($f_p_stat == 'Paid') {
  $query .= " AND total_cleared >= total_payable AND total_payable > 0";
} elseif ($f_p_stat == 'Unpaid') {
  $query .= " AND total_cleared = 0";
} elseif ($f_p_stat == 'Partial') {
  $query .= " AND total_cleared > 0 AND total_cleared < total_payable";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Global Fee Registry | AMINA GIRLS HIGH SCHOOL</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <style>
    .uppercase-data {
      text-transform: uppercase;
      color: #000;
      font-weight: bold;
    }

    .badge-status {
      font-size: 8px;
      text-transform: uppercase;
      font-weight: 700;
      padding: 3px 6px;
    }

    #printArea {
      display: flex;
      justify-content: space-between;
      gap: 15px;
    }

    .ledger-copy {
      width: 48.5%;
      border: 2.5px solid #000;
      padding: 18px;
      background: #fff;
      border-radius: 10px;
      position: relative;
      color: #000;
    }

    .ledger-copy::before {
      content: "";
      background-image: url('assets/img/AGHS Logo.png');
      background-repeat: no-repeat;
      background-position: center;
      background-size: 200px;
      opacity: 0.05;
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      z-index: 0;
    }

    .ledger-copy>* {
      position: relative;
      z-index: 1;
    }

    .school-title {
      font-size: 18px;
      font-weight: 900;
      color: #000;
      margin: 0;
    }

    .info-label {
      font-size: 10px;
      color: #333;
      font-weight: bold;
      text-transform: uppercase;
    }

    .info-val {
      font-size: 11px;
      font-weight: 900;
      color: #000;
    }

    .table-ledger {
      width: 100%;
      border-collapse: collapse !important;
      font-size: 10px !important;
      margin-top: 5px;
    }

    .table-ledger th,
    .table-ledger td {
      border: 1.5px solid #000 !important;
      padding: 4px !important;
      color: #000 !important;
    }

    .summary-box {
      border: 1.5px solid #000;
      padding: 8px;
      border-radius: 6px;
      background: rgba(255, 255, 255, 0.8);
    }

    .status-stamp {
      font-size: 16px;
      font-weight: 900;
      border: 3px double #000;
      padding: 3px 10px;
      display: inline-block;
      transform: rotate(-5deg);
      border-radius: 6px;
    }

    @media print {
      @page {
        size: A4 landscape;
        margin: 5mm;
      }

      body {
        background: #fff !important;
      }

      .no-print,
      .main-sidebar,
      .navbar,
      .modal-header,
      .btn,
      .main-content .card-header {
        display: none !important;
      }

      .main-content {
        padding: 0 !important;
        margin: 0 !important;
      }

      .ledger-copy {
        border: 1.5px solid #000;
        width: 48%;
      }

      .text-success,
      .text-danger {
        color: #000 !important;
        font-weight: bold;
      }
    }
  </style>
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <?php include 'include/navbar.php'; ?>
      <div class="main-sidebar sidebar-style-2"><?php include 'include/asidebar.php'; ?></div>

      <div class="main-content">
        <section class="section">
          <div class="card shadow-sm no-print">
            <div class="card-header border-bottom">
              <h4><i class="fas fa-file-invoice-dollar text-primary"></i> Global Fee Registry</h4>
            </div>

            <div class="card-body bg-light border-bottom">
              <form method="GET" class="row">
                <div class="col-md-2 form-group"><label>Session</label><select name="session_id" class="form-control select2">
                    <option value="">All</option><?php foreach ($sessions as $s) echo "<option value='{$s['id']}' " . ($f_sess == $s['id'] ? 'selected' : '') . ">{$s['session_name']}</option>"; ?>
                  </select></div>
                <div class="col-md-2 form-group"><label>Class</label><select name="class_id" id="f_class" class="form-control select2">
                    <option value="">All</option><?php foreach ($classes as $c) echo "<option value='{$c['id']}' " . ($f_class == $c['id'] ? 'selected' : '') . ">{$c['class_name']}</option>"; ?>
                  </select></div>
                <div class="col-md-3 form-group"><label>Registry</label><select name="student_status" class="form-control select2">
                    <option value="All">All Registered</option>
                    <option value="Active" <?= ($f_s_stat == 'Active' ? 'selected' : '') ?>>Active Only</option>
                    <option value="Passout" <?= ($f_s_stat == 'Passout' ? 'selected' : '') ?>>Passout</option>
                  </select></div>
                <div class="col-md-3 form-group"><label>Fee Status</label><select name="payment_status" class="form-control select2">
                    <option value="All">All Payments</option>
                    <option value="Paid" <?= ($f_p_stat == 'Paid' ? 'selected' : '') ?>>Paid</option>
                    <option value="Unpaid" <?= ($f_p_stat == 'Unpaid' ? 'selected' : '') ?>>Unpaid</option>
                  </select></div>
                <div class="col-md-2"><label class="d-block">&nbsp;</label><button type="submit" class="btn btn-primary btn-block">Filter</button></div>
              </form>
            </div>

            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover" id="tableExport" style="width:100%;">

                  <thead>
                    <tr>
                      <th>Reg#</th>
                      <th>Name</th>
                      <th>Bill</th>
                      <th>Paid</th>
                      <th>Balance</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($students as $row):
                      $bal = (float)$row['total_payable'] - (float)$row['total_cleared'];
                    ?>
                      <tr>
                        <td class="fw-bold"><?= $row['reg_no'] ?></td>
                        <td class="uppercase-data small"><?= $row['student_name'] ?></td>
                        <td><?= number_format($row['total_payable']) ?></td>
                        <td class="text-success"><?= number_format($row['total_cleared']) ?></td>
                        <td class="text-danger fw-bold"><?= number_format($bal) ?></td>
                        <td><?= ($row['is_passout'] ? '<span class="badge badge-success badge-status">Passout</span>' : '<span class="badge badge-primary badge-status">Active</span>') ?></td>
                        <td><button class="btn btn-dark btn-sm btnViewLedger" data-id="<?= $row['id'] ?>"><i class="fa fa-print"></i> Statement</button></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>
      </div>

      <style>
        /* 1. Print Optimization (Force White Background) */
        @media print {
          @page {
            size: A4 landscape;
            margin: 5mm;
          }

          /* Poore page aur modal layers ko white karne ke liye */
          html,
          body,
          .modal,
          .modal-open,
          .modal-content,
          .modal-body,
          #printArea {
            background: #ffffff !important;
            background-color: #ffffff !important;
            visibility: visible !important;
          }

          /* Modal ke peechay ka shadow aur extra borders khatam karne ke liye */
          .modal-backdrop,
          .modal-header,
          .modal-footer,
          .no-print,
          .main-sidebar,
          .navbar {
            display: none !important;
          }

          .modal-dialog {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
          }

          .shadow-sm {
            box-shadow: none !important;
          }

          .ledger-copy {
            border: 1.5px solid #000 !important;
          }
        }

        /* 2. Professional Layout Display */
        #printArea {
          display: flex;
          justify-content: space-between;
          align-items: stretch;
          background: #fff;
          width: 100%;
        }

        .ledger-copy {
          width: 47%;
          /* Landscape spacing adjustment */
          padding: 15px;
          background: #fff;
          border: 1.5px solid #000;
        }

        /* 3. Center Vertical Cutter Line */
        .cutter-wrapper {
          width: 4%;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: space-around;
          /* Kainchi ko barabar faslay par rakhne ke liye */
          position: relative;
        }

        .cutter-line {
          border-left: 2px dashed #000;
          height: 100%;
          position: absolute;
          left: 50%;
          top: 0;
        }

        .cutter-wrapper i {
          background: #fff;
          z-index: 10;
          padding: 5px 0;
          font-size: 16px;
          color: #000;
        }
      </style>

      <div class="modal fade" id="ledgerModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 98%;">
          <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white no-print py-2">
              <h6 class="modal-title">Fee Statement Dual Copy</h6>
              <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body p-3">
              <div id="printArea">
                <?php function renderCopy($type)
                { ?>
                  <div class="ledger-copy shadow-sm">
                    <div class="row align-items-center border-bottom border-dark pb-2 mb-2">
                      <div class="col-2"><img src="assets/img/AGHS Logo.png" style="width:50px" onerror="this.src='assets/img/favicon.ico'"></div>
                      <div class="col-7 text-center">
                        <h2 class="school-title" style="font-size:18px; font-weight:bold; margin:0;">AMINA GIRLS HIGH SCHOOL</h2>
                        <p class="m-0 font-weight-bold" style="font-size:10px;">21/MPR-LODHRAN | <?= $type ?></p>
                        <h6 class="m-0" style="font-size:12px;">Student Fee Statement</h6>
                      </div>
                      <div class="col-3"><img src="assets/img/teflogo.png" style="height:50px" onerror="this.src='assets/img/favicon.ico'"></div>
                    </div>

                    <div class="row mb-2 border-bottom border-dark pb-1">
                      <div class="col-5 border-right border-dark"><span class="info-label">Student:</span><br><span class="info-val l_name"></span></div>
                      <div class="col-3 border-right border-dark"><span class="info-label">Reg#:</span><br><span class="info-val l_reg"></span></div>
                      <div class="col-4"><span class="info-label">Class:</span><br><span class="info-val l_class"></span></div>
                    </div>

                    <div class="row">
                      <div class="col-6">
                        <table class="table-ledger">
                          <thead class="bg-light">
                            <tr>
                              <th>Fee Item</th>
                              <th class="text-right">Payable</th>
                            </tr>
                          </thead>
                          <tbody class="assigned_rows"></tbody>
                        </table>
                      </div>
                      <div class="col-6">
                        <table class="table-ledger">
                          <thead class="bg-light">
                            <tr>
                              <th>Date</th>
                              <th>Paid</th>
                              <th>Consession</th>
                            </tr>
                          </thead>
                          <tbody class="history_rows"></tbody>
                        </table>
                      </div>
                    </div>

                    <div class="summary-box mt-2 p-2 border border-dark rounded">
                      <div class="row align-items-center">
                        <div class="col-7" style="font-size:10px;">
                          Total Payable: <span class="sum_bill"></span><br>
                          Total Paid: <span class="sum_paid text-success font-weight-bold"></span> | Consession: <span class="sum_mafi"></span>
                          <h6 class="mt-1 mb-0 fw-bold">Remaining Dues: <span class="sum_bal text-danger"></span></h6>
                        </div>
                        <div class="col-5 text-center">
                          <div class="stamp_area"></div>
                        </div>
                      </div>
                    </div>

                    <div class="row mt-4 px-2" style="font-size:9px;">
                      <div class="col-6 border-top border-dark pt-1">Authorized Signature</div>
                      <div class="col-6 border-top border-dark pt-1 text-right">Printed: <span class="live-date"></span></div>
                    </div>
                  </div>
                <?php } ?>

                <?php renderCopy("OFFICE COPY"); ?>

                <div class="cutter-wrapper">
                  <div class="cutter-line"></div>
                  <i class="fa fa-scissors"></i>
                  <i class="fa fa-scissors"></i>
                  <i class="fa fa-scissors"></i>
                </div>

                <?php renderCopy("STUDENT COPY"); ?>
              </div>
            </div>

            <div class="modal-footer no-print">
              <button class="btn btn-primary btn-block btn-lg" onclick="window.print()">PRINT STATEMENT</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script>
    $(document).ready(function() {
      if ($('#tableExport').length) {
        $('#tableExport').DataTable();
      }

      $(document).on('click', '.btnViewLedger', function() {
        let sid = $(this).data('id');
        $.getJSON('manage-fee-record.php?action=get_student_ledger&student_id=' + sid, function(res) {
          $('.l_name').text(res.info.student_name.toUpperCase());
          $('.l_reg').text(res.info.reg_no);
          $('.l_class').text(res.info.class_name + " (" + (res.info.section_name || 'N/A') + ")");

          let aRow = '',
            hRow = '',
            tBill = 0,
            tPaid = 0,
            tMafi = 0;

          res.assigned.forEach(a => {
            let tick = a.is_paid ? '<b class="text-success">✓ </b>' : '';
            aRow += `<tr><td>${tick}${a.fee_title}</td><td class="text-right">${parseFloat(a.amount).toLocaleString()}</td></tr>`;
            tBill += parseFloat(a.amount);
          });
          if (res.transport_fare > 0) {
            let tTick = res.tr_paid ? '<b class="text-success">✓ </b>' : '';
            aRow += `<tr><td>${tTick}Transport Fee</td><td class="text-right">${parseFloat(res.transport_fare).toLocaleString()}</td></tr>`;
            tBill += parseFloat(res.transport_fare);
          }

          res.history.forEach(h => {
            hRow += `<tr><td>${h.payment_date}</td><td class="text-right">${parseFloat(h.amount_paid).toLocaleString()}</td><td class="text-right">${parseFloat(h.special_discount).toLocaleString()}</td></tr>`;
            tPaid += parseFloat(h.amount_paid);
            tMafi += parseFloat(h.special_discount);
          });

          let balance = tBill - (tPaid + tMafi);
          $('.assigned_rows').html(aRow);
          $('.history_rows').html(hRow || '<tr><td colspan="3" class="text-center">No Data</td></tr>');
          $('.sum_bill').text(tBill.toLocaleString());
          $('.sum_paid').text(tPaid.toLocaleString());
          $('.sum_mafi').text(tMafi.toLocaleString());
          $('.sum_bal').text(balance.toLocaleString());
          $('.live-date').text(new Date().toLocaleDateString('en-GB'));
          $('.stamp_area').html(balance <= 0 ? '<div class="status-stamp text-success">PAID</div>' : '<div class="status-stamp text-danger">DUE</div>');
          $('#ledgerModal').modal('show');
        });
      });
    });
  </script>
</body>

</html>