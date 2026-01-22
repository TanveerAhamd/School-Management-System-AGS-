<?php
require_once 'auth.php'; // Aapki database connection file

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ==========================================
// 1. SAVE / UPDATE DISCOUNT TYPE LOGIC
// ==========================================
if (isset($_POST['btn_save_discount'])) {
  $title = trim($_POST['discount_title']);
  $value = $_POST['discount_value'];
  $type  = $_POST['discount_type'];
  $update_id = isset($_POST['update_id']) && !empty($_POST['update_id']) ? $_POST['update_id'] : null;

  try {
    if ($update_id) {
      // Update Logic
      $stmt = $pdo->prepare("UPDATE discount_types SET discount_title=?, discount_value=?, discount_type=? WHERE id=?");
      $stmt->execute([$title, $value, $type, $update_id]);
      $_SESSION['swal_msg'] = ["title" => "Updated!", "text" => "Discount type updated successfully.", "type" => "success"];
    } else {
      // Insert Logic
      $stmt = $pdo->prepare("INSERT INTO discount_types (discount_title, discount_value, discount_type) VALUES (?,?,?)");
      $stmt->execute([$title, $value, $type]);
      $_SESSION['swal_msg'] = ["title" => "Saved!", "text" => "New discount type added.", "type" => "success"];
    }
  } catch (Exception $e) {
    $_SESSION['swal_msg'] = ["title" => "Error!", "text" => $e->getMessage(), "type" => "error"];
  }
  header("Location: discount-type.php");
  exit();
}

// ==========================================
// 2. DELETE DISCOUNT TYPE LOGIC
// ==========================================
if (isset($_GET['del'])) {
  $del_id = $_GET['del'];
  try {
    $pdo->prepare("DELETE FROM discount_types WHERE id = ?")->execute([$del_id]);
    $_SESSION['swal_msg'] = ["title" => "Deleted!", "text" => "Discount type removed.", "type" => "success"];
  } catch (Exception $e) {
    $_SESSION['swal_msg'] = ["title" => "Error!", "text" => "Cannot delete. Linked with student records.", "type" => "error"];
  }
  header("Location: discount-type.php");
  exit();
}

// ==========================================
// 3. FETCH DATA
// ==========================================
$discount_list = $pdo->query("SELECT * FROM discount_types ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Discount Management</title>
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
                        <h5 class="page-title mb-0">Discount Types Setup</h5>
                      </div>
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="#"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                            <li class="breadcrumb-item active">Discount Type</li>
                          </ol>
                        </nav>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <!-- Add Form -->
              <div class="col-sm-4">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fa fa-percentage"></i> Add Discount Type</p>
                  </div>
                  <form method="POST" action="discount-type.php" class="needs-validation" novalidate>
                    <div class="card-body">
                      <div class="form-group">
                        <label>Discount Title</label>
                        <input type="text" name="discount_title" class="form-control" placeholder="e.g. Sibling Discount" required>
                      </div>

                      <div class="form-group">
                        <label>Discount Value</label>
                        <input type="number" name="discount_value" class="form-control" placeholder="Value" required>
                      </div>

                      <div class="form-group">
                        <label>Value Type</label>
                        <select name="discount_type" class="form-control select2" required style="width:100%;">
                          <option value="Percentage">Percentage (%)</option>
                          <option value="Fixed">Fixed Amount</option>
                        </select>
                      </div>
                    </div>
                    <div class="card-footer text-center">
                      <button type="submit" name="btn_save_discount" class="btn btn-primary btn-block btn-sm">
                        <i class="fa fa-save"></i>&nbsp;Save Discount
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- List Table -->
              <div class="col-sm-8">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fa fa-list"></i> Discount Categories</p>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Value</th>
                            <th>Type</th>
                            <th>Options</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($discount_list as $key => $row): ?>
                            <tr>
                              <td><?= $key + 1 ?></td>
                              <td><?= $row['discount_title'] ?></td>
                              <td><?= $row['discount_value'] ?></td>
                              <td>
                                <span class="badge <?= $row['discount_type'] == 'Percentage' ? 'badge-info' : 'badge-success' ?>">
                                  <?= $row['discount_type'] ?>
                                </span>
                              </td>
                              <td>
                                <button class="btn btn-sm btn-primary btn-edit"
                                  data-id="<?= $row['id'] ?>"
                                  data-title="<?= $row['discount_title'] ?>"
                                  data-value="<?= $row['discount_value'] ?>"
                                  data-type="<?= $row['discount_type'] ?>">
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

      <!-- Edit Modal -->
      <div class="modal fade" id="editDiscountModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <form method="POST" action="discount-type.php" class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Discount</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="update_id" id="edit_id">
              <div class="form-group">
                <label>Title</label>
                <input type="text" name="discount_title" id="edit_title" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Value</label>
                <input type="number" name="discount_value" id="edit_value" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Type</label>
                <select name="discount_type" id="edit_type" class="form-control select2" style="width:100%;">
                  <option value="Percentage">Percentage (%)</option>
                  <option value="Fixed">Fixed Amount</option>
                </select>
              </div>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
              <button type="submit" name="btn_save_discount" class="btn btn-primary btn-sm">Update Changes</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    $(document).ready(function() {
      if ($('#tableExport').length) {
        $('#tableExport').DataTable({
          dom: 'Bfrtip',
          buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        });
      }

      <?php if (isset($_SESSION['swal_msg'])): ?>
        swal("<?= $_SESSION['swal_msg']['title'] ?>", "<?= $_SESSION['swal_msg']['text'] ?>", "<?= $_SESSION['swal_msg']['type'] ?>");
        <?php unset($_SESSION['swal_msg']); ?>
      <?php endif; ?>

      $('.btn-edit').on('click', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_title').val($(this).data('title'));
        $('#edit_value').val($(this).data('value'));
        $('#edit_type').val($(this).data('type')).trigger('change');
        $('#editDiscountModal').modal('show');
      });

      $('.btn-delete').on('click', function() {
        var id = $(this).data('id');
        swal({
          title: "Are you sure?",
          icon: "warning",
          buttons: true,
          dangerMode: true,
        }).then((willDelete) => {
          if (willDelete) {
            window.location.href = "discount-type.php?del=" + id;
          }
        });
      });
    });
  </script>
</body>

</html>