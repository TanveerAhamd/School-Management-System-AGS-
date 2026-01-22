<?php

/**
 * 1. DATABASE CONNECTION & AJAX HANDLERS
 */
require_once 'auth.php';

if (isset($_GET['action'])) {
  header('Content-Type: application/json');

  // Action: Fetch Sections
  if ($_GET['action'] == 'fetch_sections') {
    $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
    $stmt->execute([$_GET['class_id'] ?? 0]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
  }

  // Action: Fetch Student Ledger for Modal
  if ($_GET['action'] == 'get_student_ledger' && isset($_GET['student_id'])) {
    $st_id = $_GET['student_id'];

    $stmt_info = $pdo->prepare("SELECT s.student_name, s.reg_no, c.class_name, sec.section_name 
                                    FROM students s 
                                    LEFT JOIN classes c ON s.class_id = c.id 
                                    LEFT JOIN sections sec ON s.section_id = sec.id 
                                    WHERE s.id = ?");
    $stmt_info->execute([$st_id]);
    $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    $stmt_pay = $pdo->prepare("SELECT fp.*, COALESCE(ft.fee_title, 'Transport Fee') as fee_name 
                                   FROM fee_payments fp 
                                   LEFT JOIN fee_types ft ON fp.fee_type_id = ft.id 
                                   WHERE fp.student_id = ? ORDER BY fp.payment_date ASC");
    $stmt_pay->execute([$st_id]);
    $history = $stmt_pay->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['info' => $info, 'history' => $history]);
    exit;
  }
}

/**
 * 2. FILTER LOGIC
 */
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$all_fee_types = $pdo->query("SELECT id, fee_title FROM fee_types GROUP BY fee_title")->fetchAll();

$f_class  = $_GET['class_id'] ?? '';
$f_sec    = $_GET['section_id'] ?? '';
$f_s_stat = $_GET['student_status'] ?? 'All';
$f_p_stat = $_GET['payment_status'] ?? 'All';
$f_type   = $_GET['fee_type'] ?? '';

// Build Global Query using Subquery for status filtering
$query = "SELECT * FROM (
    SELECT s.id, s.reg_no, s.student_name, s.class_id, s.section_id, s.transport, s.route_id, 
           s.is_deleted, s.is_passout, s.is_dropout,
           c.class_name, sec.section_name,
           (IFNULL((SELECT SUM(amount) FROM fee_types WHERE class_id = s.class_id), 0) + 
            CASE WHEN s.transport = 'Yes' THEN IFNULL((SELECT fare FROM transport_allocations WHERE route_id = s.route_id LIMIT 1), 0) ELSE 0 END) as total_payable,
           IFNULL((SELECT SUM(amount_paid + special_discount) FROM fee_payments WHERE student_id = s.id), 0) as total_cleared
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN sections sec ON s.section_id = sec.id
) as ledger WHERE 1=1";

$params = [];
if (!empty($f_class)) {
  $query .= " AND class_id = ?";
  $params[] = $f_class;
}
if (!empty($f_sec)) {
  $query .= " AND section_id = ?";
  $params[] = $f_sec;
}

if ($f_s_stat == 'Active') {
  $query .= " AND is_deleted = 0";
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

if (!empty($f_type)) {
  $query .= " AND id IN (SELECT student_id FROM fee_payments WHERE fee_type_id = ?)";
  $params[] = $f_type;
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
  <title>Manage Fee Records | AGS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />
  <style>
    .badge-fee {
      width: 22px;
      height: 22px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 10px;
      border-radius: 4px;
      font-weight: bold;
      margin-right: 2px;
      color: #fff;
      cursor: help;
    }

    .uppercase-data {
      text-transform: uppercase;
      color: #000 !important;
      font-weight: bold;
    }

    .tr-circle-svg {
      width: 25px;
      height: 25px;
      vertical-align: middle;
      margin-left: 5px;
    }

    /* PROFESSIONAL LEDGER STYLES */
    #print-footer-legal {
      display: none;
    }

    @media print {
      @page {
        size: A4;
        margin: 10mm;
      }

      body {
        background: #fff !important;
        font-family: 'Arial', sans-serif;
      }

      .no-print {
        display: none !important;
      }

      .ledger-container {
        font-size: 11px !important;
        color: #000 !important;
      }

      .table-ledger {
        width: 100%;
        border-collapse: collapse !important;
        font-size: 10px !important;
      }

      .table-ledger th,
      .table-ledger td {
        border: 1px solid #333 !important;
        padding: 4px !important;
      }

      .table-ledger th {
        background-color: #f2f2f2 !important;
        -webkit-print-color-adjust: exact;
      }

      #print-footer-legal {
        display: block !important;
        position: fixed;
        bottom: 0;
        width: 100%;
        text-align: center;
        font-size: 10px;
        border-top: 1px dashed #333;
        padding-top: 10px;
        color: #555;
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
          <div class="row bg-title">
            <div class="col-12">
              <div class="card mb-3">
                <div class="card-body py-2 b-0">
                  <h5><i class="fas fa-history"></i> Manage Fee Collection Records</h5>
                </div>
              </div>
            </div>
          </div>

          <div class="row" id="fee_record_scope">
            <div class="col-12">
              <div class="card shadow-sm">
                <!-- MULTI-FILTER SECTION -->
                <div class="card-body border-bottom ">
                  <form method="GET" class="row">
                    <div class="col-md-2 form-group"><label class="small fw-bold">Fee Type</label><select name="fee_type" class="form-control select2">
                        <option value="">All Types</option><?php foreach ($all_fee_types as $ft) echo "<option value='{$ft['id']}' " . ($f_type == $ft['id'] ? 'selected' : '') . ">{$ft['fee_title']}</option>"; ?>
                      </select></div>
                    <div class="col-md-2 form-group"><label class="small fw-bold">Class</label><select name="class_id" id="f_class" class="form-control select2">
                        <option value="">All Classes</option><?php foreach ($classes as $c) echo "<option value='{$c['id']}' " . ($f_class == $c['id'] ? 'selected' : '') . ">{$c['class_name']}</option>"; ?>
                      </select></div>
                    <div class="col-md-2 form-group"><label class="small fw-bold">Section</label><select name="section_id" id="f_section" class="form-control select2">
                        <option value="">All Sections</option>
                      </select></div>
                    <div class="col-md-2 form-group"><label class="small fw-bold">S. Status</label><select name="student_status" class="form-control select2">
                        <option value="All">All Students</option>
                        <option value="Active" <?= ($f_s_stat == 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Dropout" <?= ($f_s_stat == 'Dropout') ? 'selected' : '' ?>>Dropout</option>
                        <option value="Passout" <?= ($f_s_stat == 'Passout') ? 'selected' : '' ?>>Passout</option>
                      </select></div>
                    <div class="col-md-2 form-group"><label class="small fw-bold">Fee Status</label><select name="payment_status" class="form-control select2">
                        <option value="All">All</option>
                        <option value="Paid" <?= ($f_p_stat == 'Paid') ? 'selected' : '' ?>>Paid</option>
                        <option value="Partial" <?= ($f_p_stat == 'Partial') ? 'selected' : '' ?>>Partial</option>
                        <option value="Unpaid" <?= ($f_p_stat == 'Unpaid') ? 'selected' : '' ?>>Unpaid</option>
                      </select></div>
                    <div class="col-md-2"><label class="d-block">&nbsp;</label><button type="submit" class="btn btn-primary btn-block">Search</button></div>
                  </form>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                      <thead>
                        <tr>
                          <th>Reg#</th>
                          <th>Student Name</th>
                          <th>Breakdown</th>
                          <th>Payable</th>
                          <th>Paid</th>
                          <th>Balance</th>
                          <th>Status</th>
                          <th>S.Status</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="scoped_list_container">
                        <?php foreach ($students as $row):
                          $payable = (float)$row['total_payable'];
                          $cleared = (float)$row['total_cleared'];
                          $balance = $payable - $cleared;

                          if ($cleared == 0) $p_badge = '<span class="badge badge-danger">UNPAID</span>';
                          elseif ($balance > 0) $p_badge = '<span class="badge badge-warning">PARTIAL</span>';
                          else $p_badge = '<span class="badge badge-success">PAID</span>';
                        ?>
                          <tr>
                            <td class="fw-bold"><?= $row['reg_no'] ?></td>
                            <td class="uppercase-data small"><?= $row['student_name'] ?></td>
                            <td>
                              <div class="d-flex align-items-center">
                                <?php
                                $st_f = $pdo->prepare("SELECT ft.fee_title, ft.amount, (SELECT IFNULL(SUM(amount_paid + special_discount), 0) FROM fee_payments WHERE student_id = ? AND fee_type_id = ft.id) as paid FROM fee_types ft WHERE ft.class_id = ?");
                                $st_f->execute([$row['id'], $row['class_id']]);
                                foreach ($st_f->fetchAll() as $f) {
                                  $l = substr($f['fee_title'], 0, 1);
                                  $c = ($f['paid'] >= $f['amount']) ? 'bg-success' : ($f['paid'] > 0 ? 'bg-warning' : 'bg-danger');
                                  echo "<span class='badge-fee $c' title='{$f['fee_title']}'>$l</span>";
                                }
                                if ($row['transport'] == 'Yes' && !empty($row['route_id'])) {
                                  $t_p = $pdo->query("SELECT IFNULL(SUM(amount_paid + special_discount), 0) FROM fee_payments WHERE student_id = " . $row['id'] . " AND fee_type_id = 0")->fetchColumn();
                                  $t_f = $pdo->query("SELECT fare FROM transport_allocations WHERE route_id = " . $row['route_id'])->fetchColumn();
                                  $pct = ($t_f > 0) ? min(($t_p / $t_f) * 100, 100) : 0;
                                  $clr = ($pct >= 99) ? '#63ed7a' : ($pct > 30 ? '#ffa426' : '#fc544b');
                                  $off = 56.5 - ($pct / 100) * 56.5;
                                  echo "<div class='tr-circle-svg'><svg width='25' height='25'><circle cx='12' cy='12' r='9' fill='none' stroke='#eee' stroke-width='2'/><circle cx='12' cy='12' r='9' fill='none' stroke='$clr' stroke-width='2' stroke-dasharray='56.5' stroke-dashoffset='$off' transform='rotate(-90 12 12)'/><text x='12' y='14.5' text-anchor='middle' font-size='6' font-weight='bold'>T</text></svg></div>";
                                }
                                ?>
                              </div>
                            </td>
                            <td class="fw-bold">Rs. <?= number_format($payable) ?></td>
                            <td class="text-success fw-bold">Rs. <?= number_format($cleared) ?></td>
                            <td class="text-danger fw-bold">Rs. <?= number_format($balance) ?></td>
                            <td><?= $p_badge ?></td>
                            <td>
                              <?php if ($row['is_passout']) echo '<span class="badge badge-success">PASSOUT</span>';
                              elseif ($row['is_dropout']) echo '<span class="badge badge-danger">DROPOUT</span>';
                              elseif ($row['is_deleted']) echo '<span class="badge badge-dark">ARCHIVED</span>';
                              else echo '<span class="badge badge-primary">ACTIVE</span>'; ?>
                            </td>
                            <td><button class="btn btn-sm btn-info btnViewLedger" data-id="<?= $row['id'] ?>"> Detail</button></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <?php include 'include/footer.php'; ?>

      <!-- LEDGER MODAL -->
      <div class="modal fade" id="ledgerModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content shadow-lg">
            <div id="print_section_scoped">
              <div class="modal-header bg-light no-print">
                <h5 class="modal-title text-dark">Student Account Statement</h5>
                <button class="close" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body bg-white ledger-container">
                <div class="row border-bottom pb-2 mb-3 align-items-center">
                  <div class="col-sm-2 text-center"><img src="assets/img/agslogo.png" style="width:65px"></div>
                  <div class="col-sm-7 text-center">
                    <h4 class="m-0 fw-bold">Amina Girls Degree College</h4>
                    <p class="m-0 small">Gailywal 21-MPR lodhran</p>
                    <h6 class="mt-1 mb-0 uppercase-data" id="l_st_info"></h6>
                  </div>
                  <div class="col-sm-3 text-right">
                    <div class="p-2 border rounded bg-light">
                      <small class="d-block fw-bold text-muted">Current Dues</small>
                      <h5 class="text-danger mb-0">Rs. <span id="l_total_bal">0</span></h5>
                    </div>
                  </div>
                </div>
                <table class="table table-sm table-bordered table-ledger uppercase-data">
                  <thead>
                    <tr class="bg-light">
                      <th>Invoice</th>
                      <th>Date</th>
                      <th>Fee Type</th>
                      <th>Payable</th>
                      <th>Paid</th>
                      <th>Concession</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="l_table_rows"></tbody>
                </table>
                <!-- PRINT FOOTER -->
                <div id="print-footer-legal">
                  <p class="mb-0">This is a computer-generated account statement produced on <strong><?= date('d-M-Y H:i A') ?></strong>.</p>
                  <p><strong>Errors and Omissions are Excepted (E&OE).</strong> Accounts Department, AGS Lodhran.</p>
                </div>
              </div>
            </div>
            <div class="modal-footer no-print">
              <button type="button" class="btn btn-dark btn-block shadow" onclick="printLedgerReport()"><i class="fa fa-print"></i> PRINT OFFICIAL LEDGER</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    $(document).ready(function() {
      // PRELOADER FALLBACK
      $(window).on('load', function() {
        $('.loader').fadeOut('slow');
      });
      setTimeout(function() {
        $('.loader').fadeOut('slow');
      }, 2000);

      if ($('#tableExport').length) {
        $('#tableExport').DataTable({
          dom: 'Bfrtip',
          buttons: ['copy', 'excel', 'pdf', 'print']
        });
      }

      // Scoped Section Load
      $('#fee_record_scope #f_class').on('change', function() {
        let cid = $(this).val();
        $.getJSON('manage-fee-record.php?action=fetch_sections&class_id=' + cid, function(data) {
          let h = '<option value="">All Sections</option>';
          data.forEach(d => h += `<option value="${d.id}">${d.section_name}</option>`);
          $('#f_section').html(h);
        });
      });

      // SCOPED MODAL DATA FETCH
      $('#scoped_list_container').on('click', '.btnViewLedger', function() {
        let sid = $(this).data('id');
        $('#l_table_rows').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');

        $.getJSON('manage-fee-record.php?action=get_student_ledger&student_id=' + sid, function(res) {
          $('#l_st_info').text(res.info.student_name + " | REG: " + res.info.reg_no + " | " + res.info.class_name);

          let h = '',
            tPay = 0,
            tPaid = 0,
            tDisc = 0;
          res.history.forEach(p => {
            h += `<tr><td>${p.invoice_no}</td><td>${p.payment_date}</td><td>${p.fee_name}</td><td>${p.amount_payable}</td><td class="text-success">${p.amount_paid}</td><td>${p.special_discount}</td><td>${p.payment_status}</td></tr>`;
            tPay += parseFloat(p.amount_payable);
            tPaid += parseFloat(p.amount_paid);
            tDisc += parseFloat(p.special_discount);
          });
          $('#l_table_rows').html(h || '<tr><td colspan="7" class="text-center">No transactions found.</td></tr>');
          $('#l_total_bal').text((tPay - (tPaid + tDisc)).toLocaleString());
          $('#ledgerModal').modal('show');
        });
      });
    });

    function printLedgerReport() {
      var printContents = document.getElementById('print_section_scoped').innerHTML;
      var printWin = window.open('', '', 'height=800,width=1000');
      printWin.document.write('<html><head><title>Account Ledger</title><link rel="stylesheet" href="assets/css/app.min.css">');
      printWin.document.write('<style>body{padding:25px; background:#fff; font-family:Arial;} .no-print{display:none;} #print-footer-legal{display:block !important; margin-top:50px;} .table-ledger td{font-size:11px !important; padding:5px !important;}</style></head><body>');
      printWin.document.write(printContents);
      printWin.document.write('</body></html>');
      printWin.document.close();
      setTimeout(function() {
        printWin.print();
        printWin.close();
      }, 700);
    }
  </script>
</body>

</html>