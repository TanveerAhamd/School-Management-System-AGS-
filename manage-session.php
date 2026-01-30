<?php
require_once 'auth.php';

// Session check
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

// ==========================================
// 1. CREATE / UPDATE / DELETE LOGIC
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // --- ADD SESSION ---
  if (isset($_POST['btn_save'])) {
    $session_name = trim($_POST['s_name']); // e.g., 2025-26
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($session_name)) {
      $stmt = $pdo->prepare("INSERT INTO academic_sessions (session_name, is_active) VALUES (?, ?)");
      if ($stmt->execute([$session_name, $is_active])) {
        $_SESSION['msg'] = "Session successfully add ho gaya!";
        $_SESSION['msg_title'] = "Success!";
        $_SESSION['msg_type'] = "success";
      }
    }
    header("Location: $current_page");
    exit();
  }

  // --- UPDATE SESSION ---
  if (isset($_POST['btn_update'])) {
    $id = $_POST['u_id'];
    $session_name = trim($_POST['u_name']);
    $is_active = isset($_POST['u_active']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE academic_sessions SET session_name = ?, is_active = ? WHERE id = ?");
    if ($stmt->execute([$session_name, $is_active, $id])) {
      $_SESSION['msg'] = "Session record update ho gaya!";
      $_SESSION['msg_title'] = "Updated!";
      $_SESSION['msg_type'] = "success";
    }
    header("Location: $current_page");
    exit();
  }

  // --- DELETE SESSION ---
  if (isset($_POST['btn_delete'])) {
    $id = $_POST['d_id'];
    $stmt = $pdo->prepare("DELETE FROM academic_sessions WHERE id = ?");
    if ($stmt->execute([$id])) {
      $_SESSION['msg'] = "Session delete kar diya gaya!";
      $_SESSION['msg_title'] = "Deleted!";
      $_SESSION['msg_type'] = "success";
    }
    header("Location: $current_page");
    exit();
  }
}

// ==========================================
// 2. FETCH DATA (Sessions)
// ==========================================
$stmt_s = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC");
$sessions_list = $stmt_s->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Manage Sessions</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
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
                        <h5 class="page-title mb-0">Manage Sessions</h5>
                      </div>
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="#"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                            <li class="breadcrumb-item"><a href="#"><i class="far fa-calendar-alt"></i> Sessions</a></li>
                            <li class="breadcrumb-item active"><i class="fas fa-list"></i> Manage</li>
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
                    <p class="mb-0 font-weight-bold"><i class="fa fa-plus"></i>&nbsp;Add Session</p>
                  </div>
                  <form method="POST" class="needs-validation" novalidate="">
                    <div class="card-body">
                      <div class="form-group">
                        <label>Session Name</label>
                        <input type="text" name="s_name" class="form-control" placeholder="e.g. 2025-26" required>
                        <div class="invalid-feedback">Enter session name (e.g. 2025-26)</div>
                      </div>
                      <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" checked>
                          <label class="custom-control-label" for="is_active">Set as Active Session</label>
                        </div>
                      </div>
                    </div>
                    <div class="card-footer text-center">
                      <button type="submit" name="btn_save" class="btn btn-info btn-block btn-rounded btn-sm">
                        <i class="fa fa-plus"></i>&nbsp;Save Session
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <div class="col-sm-8">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold transform-uppercase"> <i class="fa fa-list"></i>&nbsp;List Sessions</p>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Session Name</th>
                            <th>Status</th>
                            <th>Options</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $count = 1;
                          foreach ($sessions_list as $row) { ?>
                            <tr>
                              <td><?= $count++ ?></td>
                              <td><?= htmlspecialchars($row['session_name']) ?></td>
                              <td>
                                <?= $row['is_active'] == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>' ?>
                              </td>
                              <td>
                                <button class="btn btn-sm btn-primary editBtn"
                                  data-id="<?= $row['id'] ?>"
                                  data-name="<?= $row['session_name'] ?>"
                                  data-active="<?= $row['is_active'] ?>">
                                  <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="<?= $row['id'] ?>">
                                  <i class="fa fa-trash"></i>
                                </button>
                              </td>
                            </tr>
                          <?php } ?>
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

      <div class="modal fade" id="editSessionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Session</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" class="needs-validation" novalidate="">
              <input type="hidden" name="u_id" id="u_id">
              <div class="modal-body">
                <div class="form-group">
                  <label>Session Name</label>
                  <input type="text" name="u_name" id="u_name" class="form-control" required>
                </div>
                <div class="form-group">
                  <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="u_active" class="custom-control-input" id="u_active">
                    <label class="custom-control-label" for="u_active">Is Active?</label>
                  </div>
                </div>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="submit" name="btn_update" class="btn btn-info btn-sm"><i class="fa fa-save"></i> Update</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade" id="deleteSessionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirm Delete</h5>
              <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST">
              <input type="hidden" name="d_id" id="d_id">
              <div class="modal-body text-center">
                <p><strong>Are you sure?</strong></p>
                <p class="text-muted small">This action cannot be undone.</p>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">No</button>
                <button type="submit" name="btn_delete" class="btn btn-danger btn-sm">Yes, Delete</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>

  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.flash.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>
  <script src="assets/js/page/datatables.js"></script>

  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    $(document).on('click', '.editBtn', function() {
      $('#u_id').val($(this).data('id'));
      $('#u_name').val($(this).data('name'));
      // Checkbox status handle karna
      if ($(this).data('active') == 1) {
        $('#u_active').prop('checked', true);
      } else {
        $('#u_active').prop('checked', false);
      }
      $('#editSessionModal').modal('show');
    });

    $(document).on('click', '.deleteBtn', function() {
      $('#d_id').val($(this).data('id'));
      $('#deleteSessionModal').modal('show');
    });

    <?php if (isset($_SESSION['msg'])): ?>
      swal({
        title: "<?= $_SESSION['msg_title'] ?>",
        text: "<?= $_SESSION['msg'] ?>",
        icon: "<?= $_SESSION['msg_type'] ?>",
        timer: 2000,
        buttons: false
      });
      <?php unset($_SESSION['msg']);
      unset($_SESSION['msg_title']);
      unset($_SESSION['msg_type']); ?>
    <?php endif; ?>
  </script>
</body>

</html>