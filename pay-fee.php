<?php

/**
 * 1. DATABASE CONNECTION & AJAX HANDLERS
 */
require_once 'auth.php';

// AJAX Handlers at the top
if (isset($_GET['action'])) {
  ob_clean();
  header('Content-Type: application/json');

  if ($_GET['action'] == 'get_fee_details' && isset($_GET['student_id'])) {
    try {
      $st_id = $_GET['student_id'];
      $stmt = $pdo->prepare("SELECT id, class_id, reg_no, session, transport, route_id FROM students WHERE id = ?");
      $stmt->execute([$st_id]);
      $st = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$st) {
        throw new Exception('Student not found');
      }

      $sql_fees = "SELECT ft.id, ft.fee_title, ft.amount,
                    (SELECT IFNULL(SUM(amount_paid + special_discount), 0) 
                     FROM fee_payments 
                     WHERE student_id = ? AND fee_type_id = ft.id) as total_covered
                    FROM fee_types ft 
                    WHERE ft.class_id = ?";
      $stmt_fee = $pdo->prepare($sql_fees);
      $stmt_fee->execute([$st_id, $st['class_id']]);
      $all_fees = $stmt_fee->fetchAll(PDO::FETCH_ASSOC);

      $available_fees = [];
      foreach ($all_fees as $f) {
        $rem = (float)$f['amount'] - (float)$f['total_covered'];
        if ($rem > 0) {
          $f['remaining'] = $rem;
          $available_fees[] = $f;
        }
      }

      if ($st['transport'] == 'Yes' && !empty($st['route_id'])) {
        $stmt_tr = $pdo->prepare("SELECT fare FROM transport_allocations WHERE route_id = ? LIMIT 1");
        $stmt_tr->execute([$st['route_id']]);
        $tr_fare = $stmt_tr->fetchColumn();
        if ($tr_fare) {
          $stmt_tr_paid = $pdo->prepare("SELECT IFNULL(SUM(amount_paid + special_discount), 0) FROM fee_payments WHERE student_id = ? AND fee_type_id = 0");
          $stmt_tr_paid->execute([$st_id]);
          $tr_covered = $stmt_tr_paid->fetchColumn();
          $tr_rem = (float)$tr_fare - (float)$tr_covered;
          if ($tr_rem > 0) {
            $available_fees[] = ['id' => 0, 'fee_title' => 'Transport Fee', 'amount' => $tr_fare, 'remaining' => $tr_rem];
          }
        }
      }

      $reg_num = preg_replace('/[^0-9]/', '', $st['reg_no']);
      $v_count = $pdo->query("SELECT COUNT(id) FROM fee_payments WHERE student_id = $st_id")->fetchColumn() + 1;
      $auto_invoice = "INV-" . date('y') . str_pad($reg_num, 4, '0', STR_PAD_LEFT) . str_pad($v_count, 2, '0', STR_PAD_LEFT);

      echo json_encode([
        'status' => 'success',
        'invoice' => $auto_invoice,
        'fees' => $available_fees,
        'session_id' => $st['session']
      ]);
    } catch (Exception $e) {
      echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
  }
}

/**
 * 2. SAVE FEE PAYMENT
 */
$payment_success = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_collect_fee'])) {
  try {
    $sql = "INSERT INTO fee_payments (invoice_no, student_id, fee_type_id, session_id, amount_payable, discount_id, special_discount, amount_paid, remarks, payment_status, payment_date) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?)";
    $pdo->prepare($sql)->execute([
      $_POST['invoice_no'],
      $_POST['student_id'],
      $_POST['fee_type_id'],
      $_POST['session_id'],
      $_POST['amount_payable'],
      null,
      $_POST['special_discount'],
      $_POST['amount_paid'],
      $_POST['remarks'],
      $_POST['payment_status'],
      date('Y-m-d')
    ]);
    $payment_success = true;
  } catch (Exception $e) {
    $error_msg = $e->getMessage();
  }
}

/**
 * 3. GLOBAL FETCH LOGIC
 */
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$sections = $pdo->query("SELECT * FROM sections")->fetchAll();

$filter_class = $_GET['class_id'] ?? '';
$filter_sec   = $_GET['section_id'] ?? '';
$filter_stat  = $_GET['p_status'] ?? 'All';
$filter_st_status = $_GET['student_status'] ?? 'All';

$query = "SELECT * FROM (
            SELECT s.id, s.reg_no, s.student_name, s.guardian_name, s.class_id, s.section_id, c.class_name, sec.section_name,
            s.is_deleted, s.is_passout, s.is_dropout,
            (IFNULL((SELECT SUM(amount) FROM fee_types WHERE class_id = s.class_id), 0) + 
             CASE WHEN s.transport = 'Yes' THEN IFNULL((SELECT fare FROM transport_allocations WHERE route_id = s.route_id LIMIT 1), 0) ELSE 0 END) as total_payable,
            IFNULL((SELECT SUM(amount_paid + special_discount) FROM fee_payments WHERE student_id = s.id), 0) as total_cleared
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN sections sec ON s.section_id = sec.id
          ) as temp_table WHERE 1=1";

$params = [];
if (!empty($filter_class)) {
  $query .= " AND class_id = ?";
  $params[] = $filter_class;
}
if (!empty($filter_sec)) {
  $query .= " AND section_id = ?";
  $params[] = $filter_sec;
}

if ($filter_stat == 'Unpaid') {
  $query .= " AND total_cleared = 0 AND total_payable > 0";
} elseif ($filter_stat == 'Partial') {
  $query .= " AND total_cleared > 0 AND total_cleared < total_payable";
} elseif ($filter_stat == 'Paid') {
  $query .= " AND total_cleared >= total_payable AND total_payable > 0";
}

if ($filter_st_status == 'Active') {
  $query .= " AND is_deleted = 0 AND is_passout = 0 AND is_dropout = 0";
} elseif ($filter_st_status == 'Passout') {
  $query .= " AND is_passout = 1";
} elseif ($filter_st_status == 'Dropout') {
  $query .= " AND is_dropout = 1";
} elseif ($filter_st_status == 'Archived') {
  $query .= " AND is_deleted = 1 AND is_passout = 0 AND is_dropout = 0";
}

$list = $pdo->prepare($query);
$list->execute($params);
$students_list = $list->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Fee Collection | AGHS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="icon" href="assets/img/favicon.png">
  <style>
      .badge-status { font-size: 8px; text-transform: uppercase; font-weight: 700; padding: 3px 6px; }
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
          <div class="section-body">
            <div class="card shadow-sm">
              <div class="card-header border-bottom">
                <h4><i class="fas fa-hand-holding-usd text-primary"></i> Global Fee Registry</h4>
              </div>

              <!-- GLOBAL FILTERS -->
              <div class="card-body border-bottom bg-light">
                <form method="GET" class="row">
                  <div class="col-md-2">
                    <select name="class_id" class="form-control select2">
                      <option value="">All Classes</option><?php foreach ($classes as $c) echo "<option value='{$c['id']}' " . ($filter_class == $c['id'] ? 'selected' : '') . ">{$c['class_name']}</option>"; ?>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <select name="section_id" class="form-control select2">
                      <option value="">All Sections</option><?php foreach ($sections as $sec) echo "<option value='{$sec['id']}' " . ($filter_sec == $sec['id'] ? 'selected' : '') . ">{$sec['section_name']}</option>"; ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <select name="student_status" class="form-control select2">
                      <option value="All">All Registered</option>
                      <option value="Active" <?= ($filter_st_status == 'Active') ? 'selected' : '' ?>>Active Only</option>
                      <option value="Passout" <?= ($filter_st_status == 'Passout') ? 'selected' : '' ?>>Passout Only</option>
                      <option value="Dropout" <?= ($filter_st_status == 'Dropout') ? 'selected' : '' ?>>Dropout Only</option>
                      <option value="Archived" <?= ($filter_st_status == 'Archived') ? 'selected' : '' ?>>Archived Only</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <select name="p_status" class="form-control select2">
                      <option value="All">All Payment Status</option>
                      <option value="Unpaid" <?= ($filter_stat == 'Unpaid') ? 'selected' : '' ?>>Unpaid</option>
                      <option value="Partial" <?= ($filter_stat == 'Partial') ? 'selected' : '' ?>>Partial</option>
                      <option value="Paid" <?= ($filter_stat == 'Paid') ? 'selected' : '' ?>>Paid</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-sync"></i> Fetch List</button>
                  </div>
                </form>
              </div>

              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                    <thead>
                      <tr>
                        <th>Reg#</th>
                        <th>Student Name</th>
                        <th>Guardian</th>
                        <th>Class (Sec)</th>
                        <th>Registry Status</th>
                        <th>Payable</th>
                        <th>Cleared</th>
                        <th>Balance</th>
                        <th>Fee Status</th>
                        <th class="no-export text-center">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($students_list as $row):
                        $payable = (float)$row['total_payable'];
                        $cleared = (float)$row['total_cleared'];
                        $balance = $payable - $cleared;

                        // Fee Status Logic
                        if ($payable > 0 && $cleared >= $payable) {
                          $f_status = '<span class="badge badge-success">Full Paid</span>';
                        } elseif ($cleared > 0) {
                          $f_status = '<span class="badge badge-warning">Partial Pay</span>';
                        } else {
                          $f_status = '<span class="badge badge-danger">Unpaid</span>';
                        }

                        // Registry Status Logic (Original Identity Mapping)
                        if($row['is_passout']==1) { 
                            $r_status = '<span class="badge badge-success badge-status">Passout</span>'; 
                            $is_locked = true; 
                        } elseif($row['is_dropout']==1) { 
                            $r_status = '<span class="badge badge-danger badge-status">Dropout</span>'; 
                            $is_locked = true; 
                        } elseif($row['is_deleted']==1) { 
                            $r_status = '<span class="badge badge-secondary badge-status">Archived</span>'; 
                            $is_locked = true; 
                        } else { 
                            $r_status = '<span class="badge badge-primary badge-status">Active</span>'; 
                            $is_locked = false; 
                        }
                      ?>
                        <tr>
                          <td class="fw-bold"><?= $row['reg_no'] ?></td>
                          <td class="text-uppercase small font-weight-bold"><?= $row['student_name'] ?></td>
                          <td><?= $row['guardian_name'] ?></td>
                          <td><?= $row['class_name'] ?> (<?= $row['section_name'] ?>)</td>
                          <td><?= $r_status ?></td>
                          <td><?= number_format($payable) ?></td>
                          <td class="text-success"><?= number_format($cleared) ?></td>
                          <td class="text-danger font-weight-bold"><?= number_format($balance) ?></td>
                          <td><?= $f_status ?></td>
                          <td class="text-center">
                            <?php if ($is_locked): ?>
                              <!-- Lock icon if status is Dropout, Passout or Archived -->
                              <button class="btn btn-light btn-sm" disabled title="Fee collection locked for processed records"><i class="fa fa-lock text-muted"></i></button>
                            <?php elseif ($balance > 0): ?>
                              <button type="button" class="btn btn-info btn-sm btnCollect" data-id="<?= $row['id'] ?>" data-name="<?= $row['student_name'] ?>" data-class="<?= $row['class_name'] ?>">Pay Fee</button>
                            <?php else: ?>
                              <i class="fa fa-check-circle text-success" style="font-size: 1.2rem;" title="Full Paid"></i>
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

      <!-- COLLECT FEE MODAL -->
      <div class="modal fade" id="collectFeeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <form method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">Record Payment</h5>
              <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="student_id" id="m_st_id">
              <input type="hidden" name="session_id" id="m_sess_id">
              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="small fw-bold">Invoice #</label>
                  <input type="text" name="invoice_no" id="m_invoice" class="form-control" readonly>
                </div>
                <div class="col-md-6 form-group">
                  <label class="small fw-bold">Student Name</label>
                  <input type="text" id="m_st_name" class="form-control-plaintext border-bottom font-weight-bold" readonly>
                </div>
              </div>
              <div class="form-group">
                <label class="small fw-bold">Fee Type</label>
                <select name="fee_type_id" id="m_fee_type" class="form-control select2" style="width:100%" required></select>
              </div>
              <div class="form-group">
                <label class="small fw-bold text-danger">Total Item Dues</label>
                <input type="number" name="amount_payable" id="m_rem_bal" class="form-control bg-light font-weight-bold" readonly>
              </div>
              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="small fw-bold text-info">Fee Mafi (Discount)</label>
                  <input type="number" name="special_discount" id="m_special" value="0" class="form-control border-info">
                </div>
                <div class="col-md-6 form-group">
                  <label class="small fw-bold text-success">Net Cash Received</label>
                  <input type="number" name="amount_paid" id="m_paid" class="form-control border-success font-weight-bold" required>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="small fw-bold">Status</label>
                  <select name="payment_status" id="m_p_status" class="form-control">
                    <option value="Paid">Fully Paid</option>
                    <option value="Partial">Partial</option>
                  </select>
                </div>
                <div class="col-md-6 form-group">
                  <label class="small fw-bold">Remaining Balance</label>
                  <input type="text" id="m_net_rem" class="form-control bg-light font-weight-bold" readonly value="0">
                </div>
              </div>
              <div class="form-group mb-0">
                <label class="small fw-bold">Remarks</label>
                <textarea name="remarks" class="form-control" rows="2"></textarea>
              </div>
            </div>
            <div class="modal-footer"><button type="submit" name="btn_collect_fee" id="btnSubmit" class="btn btn-primary btn-block shadow-lg">Save Payment</button></div>
          </form>
        </div>
      </div>
      <?php include 'include/footer.php'; ?>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/js/page/datatables.js"></script>
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    $(document).ready(function() {
      <?php if ($payment_success): ?>
        swal("Success!", "Fee recorded and balance updated.", "success");
      <?php endif; ?>

      $(document).on('click', '.btnCollect', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let cls = $(this).data('class');
        $('#m_st_id').val(id);
        $('#m_st_name').val(name + ' (' + cls + ')');

        $.ajax({
          url: window.location.href,
          method: 'GET',
          data: { action: 'get_fee_details', student_id: id },
          dataType: 'json',
          success: function(res) {
            if(res.status == 'success') {
                $('#m_invoice').val(res.invoice);
                $('#m_sess_id').val(res.session_id);
                let f_opt = '<option value="">-- Select Fee Item --</option>';
                res.fees.forEach(f => f_opt += `<option value="${f.id}" data-rem="${f.remaining}">${f.fee_title} (Rs. ${f.remaining})</option>`);
                $('#m_fee_type').html(f_opt);
                $('#collectFeeModal').modal('show');
            }
          }
        });
      });

      function syncCalculations() {
        let totalDues = parseFloat($('#m_rem_bal').val()) || 0;
        let mafi = parseFloat($('#m_special').val()) || 0;
        let netPayable = totalDues - mafi;
        if(netPayable < 0) netPayable = 0;
        $('#m_paid').val(netPayable);
        updateBalance();
      }

      function updateBalance() {
        let totalDues = parseFloat($('#m_rem_bal').val()) || 0;
        let mafi = parseFloat($('#m_special').val()) || 0;
        let cashReceived = parseFloat($('#m_paid').val()) || 0;
        let finalRemaining = totalDues - mafi - cashReceived;
        $('#m_net_rem').val(finalRemaining.toFixed(0));
        if (finalRemaining <= 0) { $('#m_p_status').val('Paid'); } else { $('#m_p_status').val('Partial'); }
      }

      $(document).on('change', '#m_fee_type', function() {
        let rem = $(this).find(':selected').data('rem') || 0;
        $('#m_rem_bal').val(rem);
        $('#m_special').val(0);
        syncCalculations();
      });

      $(document).on('input', '#m_special', function() { syncCalculations(); });
      $(document).on('input', '#m_paid', function() { updateBalance(); });
    });
  </script>
</body>
</html>