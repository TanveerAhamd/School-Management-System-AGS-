<?php
require_once 'auth.php'; // Aapki database connection file

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 1. SAVE / UPDATE FEE TYPE LOGIC
// ==========================================
if (isset($_POST['btn_save_fee'])) {
    $title = trim($_POST['fee_title']);
    $amount = $_POST['amount'];
    $class_id = $_POST['class_id'];
    $due_date = $_POST['due_date'];
    $update_id = isset($_POST['update_id']) && !empty($_POST['update_id']) ? $_POST['update_id'] : null;

    try {
        if ($update_id) {
            // Update Logic
            $stmt = $pdo->prepare("UPDATE fee_types SET fee_title=?, amount=?, class_id=?, due_date=? WHERE id=?");
            $stmt->execute([$title, $amount, $class_id, $due_date, $update_id]);
            $_SESSION['swal_msg'] = ["title" => "Updated!", "text" => "Fee type has been updated.", "type" => "success"];
        } else {
            // Insert Logic
            $stmt = $pdo->prepare("INSERT INTO fee_types (fee_title, amount, class_id, due_date) VALUES (?,?,?,?)");
            $stmt->execute([$title, $amount, $class_id, $due_date]);
            $_SESSION['swal_msg'] = ["title" => "Saved!", "text" => "New fee type added successfully.", "type" => "success"];
        }
    } catch (Exception $e) {
        $_SESSION['swal_msg'] = ["title" => "Error!", "text" => $e->getMessage(), "type" => "error"];
    }
    header("Location: fee-type.php");
    exit();
}

// ==========================================
// 2. DELETE FEE TYPE LOGIC
// ==========================================
if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    try {
        $pdo->prepare("DELETE FROM fee_types WHERE id = ?")->execute([$del_id]);
        $_SESSION['swal_msg'] = ["title" => "Deleted!", "text" => "Fee type has been removed.", "type" => "success"];
    } catch (Exception $e) {
        $_SESSION['swal_msg'] = ["title" => "Error!", "text" => "Linked data exists in other tables.", "type" => "error"];
    }
    header("Location: fee-type.php");
    exit();
}

// ==========================================
// 3. FETCH DATA (Classes & Fee Types)
// ==========================================
// Aapki existing 'classes' table se data uthana
$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY id ASC")->fetchAll();

// Fee Types with Class Name Join
$fee_list = $pdo->query("SELECT f.*, c.class_name 
                         FROM fee_types f 
                         LEFT JOIN classes c ON f.class_id = c.id 
                         ORDER BY f.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Fee Type Management</title>
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
      <div class="navbar-bg"></div>
      
      <?php include 'include/navbar.php'; ?>

      <div class="main-sidebar sidebar-style-2">
        <?php include 'include/asidebar.php'; ?>
      </div>

      <div class="main-content">
        <section class="section">
          <div class="section-body">
            
            <div class="row bg-title">
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-body py-2 b-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                      <div class="mb-2 mb-md-0">
                        <h5 class="page-title mb-0">Fee Types Setup</h5>
                      </div>
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="#"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                            <li class="breadcrumb-item active">Fee Type</li>
                          </ol>
                        </nav>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-4">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fa fa-plus"></i> Add New Fee Type</p>
                  </div>
                  <form method="POST" action="fee-type.php" class="needs-validation" novalidate>
                    <div class="card-body">
                      <div class="form-group">
                        <label>Fee Title</label>
                        <input type="text" name="fee_title" class="form-control" placeholder="e.g. Monthly Tuition Fee" required>
                        <div class="invalid-feedback">Please enter fee title.</div>
                      </div>

                      <div class="form-group">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" min="0" required>
                        <div class="invalid-feedback">Please enter valid amount.</div>
                      </div>

                      <div class="form-group">
                        <label>Class</label>
                        <select name="class_id" class="form-control select2" required style="width:100%;">
                          <option value="">Select Class</option>
                          <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['class_name'] ?></option>
                          <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a class.</div>
                      </div>

                      <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                        <div class="invalid-feedback">Please select a due date.</div>
                      </div>
                    </div>
                    <div class="card-footer text-center">
                      <button type="submit" name="btn_save_fee" class="btn btn-info btn-block btn-rounded btn-sm">
                        <i class="fa fa-plus"></i>&nbsp;Save Fee Type
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <div class="col-sm-8">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold transform-uppercase"><i class="fa fa-list"></i> Registered Fee Types</p>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Fee Title</th>
                            <th>Amount</th>
                            <th>Class</th>
                            <th>Due Date</th>
                            <th>Options</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach($fee_list as $key => $row): ?>
                          <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= $row['fee_title'] ?></td>
                            <td><?= number_format($row['amount'], 0) ?></td>
                            <td><?= $row['class_name'] ?></td>
                            <td><?= $row['due_date'] ?></td>
                            <td>
                              <button class="btn btn-sm btn-primary btn-edit" 
                                data-id="<?= $row['id'] ?>"
                                data-title="<?= $row['fee_title'] ?>"
                                data-amount="<?= $row['amount'] ?>"
                                data-class="<?= $row['class_id'] ?>"
                                data-date="<?= $row['due_date'] ?>">
                                <i class="fa fa-edit"></i>
                              </button>
                              <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $row['id'] ?>">
                                <i class="fa fa-trash"></i>
                              </button>
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
          </div>
        </section>
      </div>

      <?php include 'include/footer.php'; ?>

      <div class="modal fade" id="editFeeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <form method="POST" action="fee-type.php" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Fee Details</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="update_id" id="edit_id">
                <div class="form-group">
                  <label>Fee Title</label>
                  <input type="text" name="fee_title" id="edit_title" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Amount</label>
                  <input type="number" name="amount" id="edit_amount" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Class</label>
                  <select name="class_id" id="edit_class" class="form-control select2" required style="width:100%;">
                    <?php foreach($classes as $c): ?>
                      <option value="<?= $c['id'] ?>"><?= $c['class_name'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Due Date</label>
                  <input type="date" name="due_date" id="edit_date" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
              <button type="submit" name="btn_save_fee" class="btn btn-info btn-sm">Update Changes</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>
  
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    $(document).ready(function() {
      // 1. Initialize DataTable with Export Buttons
      if ($('#tableExport').length) {
        $('#tableExport').DataTable({
          dom: 'Bfrtip',
          buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        });
      }

      // 2. Show SweetAlert Messages from Session
      <?php if(isset($_SESSION['swal_msg'])): ?>
        swal("<?= $_SESSION['swal_msg']['title'] ?>", "<?= $_SESSION['swal_msg']['text'] ?>", "<?= $_SESSION['swal_msg']['type'] ?>");
        <?php unset($_SESSION['swal_msg']); ?>
      <?php endif; ?>

      // 3. Edit Button Click Logic
      $('.btn-edit').on('click', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_title').val($(this).data('title'));
        $('#edit_amount').val($(this).data('amount'));
        $('#edit_class').val($(this).data('class')).trigger('change');
        $('#edit_date').val($(this).data('date'));
        $('#editFeeModal').modal('show');
      });

      // 4. Delete Button Click Logic
      $('.btn-delete').on('click', function() {
        var id = $(this).data('id');
        swal({
          title: "Are you sure?",
          text: "You want to delete this fee type permanently?",
          icon: "warning",
          buttons: true,
          dangerMode: true,
        }).then((willDelete) => {
          if (willDelete) {
            window.location.href = "fee-type.php?del=" + id;
          }
        });
      });
    });
  </script>
</body>
</html>