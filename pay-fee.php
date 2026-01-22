<?php

/**
 * 1. DATABASE CONNECTION & AJAX HANDLERS
 */
require_once 'auth.php';

if (isset($_GET['action'])) {
  header('Content-Type: application/json');

  // ACTION: Get student fee details for Modal
  if ($_GET['action'] == 'get_fee_details' && isset($_GET['student_id'])) {
    $st_id = $_GET['student_id'];
    $stmt = $pdo->prepare("SELECT id, class_id, reg_no, session, transport, route_id FROM students WHERE id = ?");
    $stmt->execute([$st_id]);
    $st = $stmt->fetch();

    // Har fee type ka alag se remaining balance check karna
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

    // Transport Fee logic (ID = 0)
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

    $discounts = $pdo->query("SELECT id, discount_title, discount_value, discount_type FROM discount_types")->fetchAll(PDO::FETCH_ASSOC);

    $reg_num = preg_replace('/[^0-9]/', '', $st['reg_no']);
    $v_count = $pdo->query("SELECT COUNT(id) FROM fee_payments WHERE student_id = $st_id")->fetchColumn() + 1;
    $auto_invoice = date('y') . "-" . str_pad($reg_num, 4, '0', STR_PAD_LEFT) . "-" . str_pad($v_count, 3, '0', STR_PAD_LEFT);

    echo json_encode(['invoice' => $auto_invoice, 'fees' => $available_fees, 'discounts' => $discounts, 'session_id' => $st['session']]);
    exit;
  }
}

/**
 * 2. SAVE FEE PAYMENT
 */
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
      (!empty($_POST['discount_id']) ? $_POST['discount_id'] : null),
      $_POST['special_discount'],
      $_POST['amount_paid'],
      $_POST['remarks'],
      $_POST['payment_status'],
      date('Y-m-d')
    ]);
    $success_pay = true;
  } catch (Exception $e) {
    $error_pay = $e->getMessage();
  }
}

/**
 * 3. FETCH STUDENT LIST WITH CALCULATED PAYABLE
 */
$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$sections = $pdo->query("SELECT * FROM sections")->fetchAll();

$filter_class = $_GET['class_id'] ?? '';
$filter_sec   = $_GET['section_id'] ?? '';
$filter_stat  = $_GET['p_status'] ?? 'All';

// Subquery approach: Calculate total_payable by adding class fees and transport fare
$query = "SELECT * FROM (
            SELECT s.id, s.reg_no, s.student_name, s.class_id, s.section_id, c.class_name, sec.section_name,
            -- Calculate Total Payable
            (IFNULL((SELECT SUM(amount) FROM fee_types WHERE class_id = s.class_id), 0) + 
             CASE WHEN s.transport = 'Yes' THEN IFNULL((SELECT fare FROM transport_allocations WHERE route_id = s.route_id LIMIT 1), 0) ELSE 0 END) as total_payable,
            -- Calculate Total Cleared
            IFNULL((SELECT SUM(amount_paid + special_discount) FROM fee_payments WHERE student_id = s.id), 0) as total_cleared
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN sections sec ON s.section_id = sec.id
            WHERE s.is_deleted = 0
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

// Status Filters
if ($filter_stat == 'Unpaid') {
  $query .= " AND total_cleared = 0 AND total_payable > 0";
} elseif ($filter_stat == 'Partial') {
  $query .= " AND total_cleared > 0 AND total_cleared < total_payable";
} elseif ($filter_stat == 'Paid') {
  $query .= " AND total_cleared >= total_payable AND total_payable > 0";
}

$stmt_list = $pdo->prepare($query);
$stmt_list->execute($params);
$list = $stmt_list->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Fee Collection Dashboard | AGS</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <?php include 'include/navbar.php'; ?>
      <div class="main-sidebar sidebar-style-2"><?php include 'include/asidebar.php'; ?></div>

      <div class="main-content">
        <section class="section">
          <div class="row bg-title">
            <div class="col-12">
              <div class="card mb-3">
                <div class="card-body py-2 b-0">
                  <h5><i class="fas fa-hand-holding-usd"></i> Student Fee Collection Dashboard</h5>
                </div>
              </div>
            </div>
          </div>

          <div class="row" id="fee_main_container">
            <div class="col-12">
              <div class="card shadow-sm">
                <!-- MULTI-FILTER PANEL -->
                <div class="card-body border-bottom ">
                  <form method="GET" class="row">
                    <div class="col-md-3">
                      <label class="small fw-bold">Class</label>
                      <select name="class_id" class="form-control select2">
                        <option value="">All Classes (Global Search)</option>
                        <?php foreach ($classes as $c) echo "<option value='{$c['id']}' " . ($filter_class == $c['id'] ? 'selected' : '') . ">{$c['class_name']}</option>"; ?>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="small fw-bold">Section</label>
                      <select name="section_id" class="form-control select2">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $sec) echo "<option value='{$sec['id']}' " . ($filter_sec == $sec['id'] ? 'selected' : '') . ">{$sec['section_name']}</option>"; ?>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="small fw-bold">Payment Status</label>
                      <select name="p_status" class="form-control select2">
                        <option value="All" <?= ($filter_stat == 'All') ? 'selected' : '' ?>>Show All Students</option>
                        <option value="Unpaid" <?= ($filter_stat == 'Unpaid') ? 'selected' : '' ?>>Unpaid Records</option>
                        <option value="Partial" <?= ($filter_stat == 'Partial') ? 'selected' : '' ?>>Partial Dues</option>
                        <option value="Paid" <?= ($filter_stat == 'Paid') ? 'selected' : '' ?>>Cleared (Full Paid)</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="d-block">&nbsp;</label>
                      <button type="submit" class="btn btn-primary btn-block shadow-sm"><i class="fa fa-search"></i> Fetch Records</button>
                    </div>
                  </form>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tableExport">
                      <thead>
                        <tr>
                          <th>Reg#</th>
                          <th>Student Name</th>
                          <th>Class (Sec)</th>
                          <th>Payable</th>
                          <th>Cleared</th>
                          <th>Balance</th>
                          <th>Fee Status</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($list as $row):
                          $payable = (float)$row['total_payable'];
                          $cleared = (float)$row['total_cleared'];
                          $balance = $payable - $cleared;

                          if ($cleared == 0) $st_badge = '<span class="badge badge-danger">Unpaid</span>';
                          elseif ($balance > 0) $st_badge = '<span class="badge badge-warning">Partial</span>';
                          else $st_badge = '<span class="badge badge-success">Full Paid</span>';
                        ?>
                          <tr>
                            <td class="fw-bold"><?= $row['reg_no'] ?></td>
                            <td class="text-uppercase small"><?= $row['student_name'] ?></td>
                            <td><?= $row['class_name'] ?> (<?= $row['section_name'] ?>)</td>
                            <td class="fw-bold">Rs. <?= number_format($payable) ?></td>
                            <td class="text-success">Rs. <?= number_format($cleared) ?></td>
                            <td class="text-danger">Rs. <?= number_format($balance) ?></td>
                            <td><?= $st_badge ?></td>
                            <td>
                              <?php if ($balance > 0): ?>
                                <button class="btn btn-sm btn-info btnCollect" data-id="<?= $row['id'] ?>" data-name="<?= $row['student_name'] ?>" data-class="<?= $row['class_name'] ?>">
                                  <i class="fa fa-money-bill-wave"></i> Collect
                                </button>
                              <?php else: ?>
                                <i class="fa fa-check-circle text-success"></i> Clear
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
          </div>
        </section>
      </div>

      <!-- COLLECT FEE MODAL -->
      <div class="modal fade" id="collectFeeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <form method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">Fee Collection Entry</h5>
              <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="student_id" id="m_st_id">
              <input type="hidden" name="session_id" id="m_sess_id">

              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="small fw-bold">Invoice #</label>
                  <input type="text" name="invoice_no" id="m_invoice" class="form-control border-primary fw-bold">
                </div>
                <div class="col-md-6 form-group">
                  <label class="small fw-bold">Student Name</label>
                  <input type="text" id="m_st_name" class="form-control-plaintext border-bottom" readonly>
                </div>
              </div>

              <div class="form-group">
                <label class="small fw-bold">Fee Type <span class="text-danger">*</span></label>
                <select name="fee_type_id" id="m_fee_type" class="form-control select2" style="width:100%" required></select>
              </div>

              <div class="row">
                <div class="col-md-12 form-group">
                  <label class="small fw-bold">Item Dues</label>
                  <input type="number" name="amount_payable" id="m_rem_bal" class="form-control bg-light" readonly>
                </div>
                <!-- <div class="col-md-6 form-group">
                        <label class="small fw-bold">Category Discount</label>
                        <select name="discount_id" id="m_discount" class="form-control">
                            <option value="" data-val="0" data-type="Fixed">No Discount</option>
                        </select>
                    </div> -->
              </div>

              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="small fw-bold">Special Concession</label>
                  <input type="number" name="special_discount" id="m_special" value="0" class="form-control">
                </div>
                <div class="col-md-6 form-group text-danger font-weight-bold">
                  <label class="small fw-bold">Net Cash Received</label>
                  <input type="number" name="amount_paid" id="m_paid" class="form-control border-danger" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="small fw-bold">Current Status</label>
                  <select name="payment_status" id="m_p_status" class="form-control">
                    <option value="Paid">Fully Paid</option>
                    <option value="Partial">Partial</option>
                  </select>
                </div>
                <div class="col-md-6 form-group">
                  <label class="small fw-bold text-info">New Balance</label>
                  <input type="text" id="m_net_rem" class="form-control bg-light" readonly value="0">
                </div>
              </div>
              <div class="form-group">
                <label class="small fw-bold">Remarks</label>
                <textarea name="remarks" class="form-control" rows="2"></textarea>
              </div>
            </div>
            <div class="modal-footer"><button type="submit" name="btn_collect_fee" id="btnSubmit" class="btn btn-primary btn-block shadow">Save Payment & Update Dues</button></div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="./assets/js/sweetalert2.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    $(document).ready(function() {
      $('.loader').fadeOut('slow');

      // 1. OPEN MODAL & FETCH DATA
      $('#fee_main_container').on('click', '.btnCollect', function() {
        let id = $(this).data('id');
        $('#m_st_id').val(id);
        $('#m_st_name').val($(this).data('name') + ' (' + $(this).data('class') + ')');

        $.getJSON('pay-fee.php?action=get_fee_details&student_id=' + id, function(res) {
          $('#m_invoice').val(res.invoice);
          $('#m_sess_id').val(res.session_id);

          let f_opt = '<option value="">-- Choose Item --</option>';
          res.fees.forEach(f => f_opt += `<option value="${f.id}" data-rem="${f.remaining}">${f.fee_title} (Rem: ${f.remaining})</option>`);
          $('#m_fee_type').html(f_opt);

          let d_opt = '<option value="" data-val="0" data-type="Fixed">No Discount</option>';
          res.discounts.forEach(d => {
            let label = (d.discount_type === 'Percentage') ? `(${d.discount_value}%)` : `(RS=${d.discount_value})`;
            d_opt += `<option value="${d.id}" data-val="${d.discount_value}" data-type="${d.discount_type}">${d.discount_title} ${label}</option>`;
          });
          $('#m_discount').html(d_opt);

          $('#collectFeeModal').modal('show');
        });
      });

      // 2. THE CORE LOGIC (Discount + Special = Full Clearance)
      function runCalc() {
        let rem = parseFloat($('#m_rem_bal').val()) || 0;
        let discVal = parseFloat($('#m_discount option:selected').data('val')) || 0;
        let discType = $('#m_discount option:selected').data('type');
        let spec = parseFloat($('#m_special').val()) || 0;

        // Calculate Category Discount Amount
        let discAmt = (discType === 'Percentage') ? (rem * discVal) / 100 : discVal;

        // Total Concession = Category Discount + Special Discount
        let totalConcession = discAmt + spec;

        // Auto fill Total Concession field
        $('#m_total_concession').val(totalConcession.toFixed(0));

        // Net Payable = Original Rem - Total Concession
        let netPayable = rem - totalConcession;
        if (netPayable < 0) netPayable = 0;

        // AUTO-FILL Cash Received with Net Payable to make balance ZERO
        $('#m_paid').val(netPayable.toFixed(0));

        // Final Balance Verification
        let paid = parseFloat($('#m_paid').val()) || 0;
        let finalRemaining = rem - totalConcession - paid;

        $('#m_net_rem').val(finalRemaining.toFixed(0));
        $('#m_p_status').val(finalRemaining <= 0 ? 'Paid' : 'Partial');

        // Disable save button if overpaid
        $('#btnSubmit').prop('disabled', finalRemaining < -1);
      }

      // 3. EVENT LISTENERS
      $('#m_fee_type').change(function() {
        let rem = $(this).find(':selected').data('rem') || 0;
        $('#m_rem_bal').val(rem);
        runCalc();
      });

      $('#m_discount, #m_special').on('input change', runCalc);

      // Manual override for Cash Received
      $('#m_paid').on('input', function() {
        let rem = parseFloat($('#m_rem_bal').val()) || 0;
        let totalConc = parseFloat($('#m_total_concession').val()) || 0;
        let paid = parseFloat($(this).val()) || 0;

        let finalRemaining = rem - totalConc - paid;
        $('#m_net_rem').val(finalRemaining.toFixed(0));
        $('#m_p_status').val(finalRemaining <= 0 ? 'Paid' : 'Partial');
      });
    });
  </script>
</body>

</html>