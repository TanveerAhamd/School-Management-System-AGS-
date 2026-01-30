<?php
require_once 'auth.php';

// Session check
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Current file detection
$current_page = basename($_SERVER['PHP_SELF']);

// ==========================================
// 1. CREATE / UPDATE / DELETE LOGIC
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // --- ADD TEACHER ---
  if (isset($_POST['btn_save'])) {
    $name = trim($_POST['t_name']);
    $email = trim($_POST['t_email']);
    $contact = trim($_POST['t_contact']);
    $class = !empty($_POST['t_class']) ? $_POST['t_class'] : 'Not Assigned';

    if (!empty($name) && !empty($email) && !empty($contact)) {
      $checkEmail = $pdo->prepare("SELECT id FROM teachers WHERE email = ?");
      $checkEmail->execute([$email]);

      if ($checkEmail->rowCount() > 0) {
        $_SESSION['msg'] = "Is email ka teacher pehle se majood hai!";
        $_SESSION['msg_title'] = "Duplicate Email!";
        $_SESSION['msg_type'] = "error";
      } else {
        $stmt = $pdo->prepare("INSERT INTO teachers (teacher_name, email, contact, assigned_class) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $contact, $class])) {
          if (function_exists('create_audit_log')) {
            create_audit_log($pdo, $_SESSION['admin_id'], 'ADD_TEACHER', "Added: $name ($email)");
          }
          $_SESSION['msg'] = "Teacher successfully add ho gaya!";
          $_SESSION['msg_title'] = "Success!";
          $_SESSION['msg_type'] = "success";
        }
      }
    }
    header("Location: $current_page");
    exit();
  }

  // --- UPDATE TEACHER ---
  if (isset($_POST['btn_update'])) {
    $id = $_POST['u_id'];
    $name = trim($_POST['u_name']);
    $email = trim($_POST['u_email']);
    $contact = trim($_POST['u_contact']);
    $class = !empty($_POST['u_class']) ? $_POST['u_class'] : 'Not Assigned';

    $checkEmail = $pdo->prepare("SELECT id FROM teachers WHERE email = ? AND id != ?");
    $checkEmail->execute([$email, $id]);

    if ($checkEmail->rowCount() > 0) {
      $_SESSION['msg'] = "Email update nahi ho saki, pehle se majood hai!";
      $_SESSION['msg_title'] = "Update Error!";
      $_SESSION['msg_type'] = "error";
    } else {
      $stmt = $pdo->prepare("UPDATE teachers SET teacher_name = ?, email = ?, contact = ?, assigned_class = ? WHERE id = ?");
      if ($stmt->execute([$name, $email, $contact, $class, $id])) {
        $_SESSION['msg'] = "Teacher record update ho gaya!";
        $_SESSION['msg_title'] = "Updated!";
        $_SESSION['msg_type'] = "success";
      }
    }
    header("Location: $current_page");
    exit();
  }

  // --- DELETE TEACHER ---
  if (isset($_POST['btn_delete'])) {
    $id = $_POST['d_id'];
    $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
    if ($stmt->execute([$id])) {
      $_SESSION['msg'] = "Teacher delete kar diya gaya!";
      $_SESSION['msg_title'] = "Deleted!";
      $_SESSION['msg_type'] = "success";
    }
    header("Location: $current_page");
    exit();
  }
}

// ==========================================
// 2. DATA READ (FETCHING)
// ==========================================

// Fetch All Teachers
try {
  $stmt_fetch = $pdo->query("SELECT * FROM teachers ORDER BY id DESC");
  $teachers_list = $stmt_fetch->fetchAll();
} catch (Exception $e) {
  $teachers_list = [];
}

// FETCH CLASSES FOR DROPDOWN (NEW ADDITION)
try {
  $stmt_classes = $pdo->query("SELECT class_name FROM classes ORDER BY CAST(numeric_name AS UNSIGNED) ASC");
  $classes_dropdown = $stmt_classes->fetchAll();
} catch (Exception $e) {
  $classes_dropdown = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Manage Teacher</title>
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
                        <h5 class="page-title mb-0">Manage Teacher</h5>
                      </div>
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item">
                              <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a>
                            </li>
                            <li class="breadcrumb-item">
                              <a href="#"><i class="far fa-file"></i> Manage Teacher</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                              <i class="fas fa-list"></i> Teacher
                            </li>
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
                    <p class="mb-0 font-weight-bold">
                      <i class="fa fa-plus"></i>&nbsp;Add Teacher
                    </p>
                  </div>
                  <form method="POST" class="needs-validation" novalidate="">
                    <div class="card-body">
                      <div class="form-group">
                        <label>Teacher Name</label>
                        <input type="text" name="t_name" class="form-control" placeholder="Enter teacher name" required="">
                        <div class="invalid-feedback">What's Teacher name?</div>
                      </div>
                      <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="t_email" class="form-control" placeholder="Enter unique email" required="">
                        <div class="invalid-feedback">A valid unique email is required.</div>
                      </div>
                      <div class="form-group">
                        <label>Contact</label>
                        <input type="text" name="t_contact" class="form-control" placeholder="Enter contact number" required="">
                        <div class="invalid-feedback">What's Teacher contact?</div>
                      </div>
                      <div class="form-group">
                        <label>Class</label>
                        <select name="t_class" class="form-control select2">
                          <option value="">Select Class</option>
                          <?php foreach ($classes_dropdown as $cls): ?>
                            <option value="<?= htmlspecialchars($cls['class_name']) ?>"><?= htmlspecialchars($cls['class_name']) ?></option>
                          <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a class</div>
                      </div>
                    </div>
                    <div class="card-footer text-center">
                      <button type="submit" name="btn_save" class="btn btn-info btn-block btn-rounded btn-sm">
                        <i class="fa fa-plus"></i>&nbsp;Save
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <div class="col-sm-8">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold transform-uppercase"> <i class="fa fa-list"></i>&nbsp;List Teacher</p>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Teacher Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Class</th>
                            <th>Options</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $count = 1;
                          foreach ($teachers_list as $row) { ?>
                            <tr>
                              <td><?= $count++; ?></td>
                              <td><?= htmlspecialchars($row['teacher_name']); ?></td>
                              <td><?= htmlspecialchars($row['email']); ?></td>
                              <td><?= htmlspecialchars($row['contact']); ?></td>
                              <td><span class="badge badge-outline-primary"><?= htmlspecialchars($row['assigned_class']); ?></span></td>
                              <td>
                                <button class="btn btn-sm btn-primary editBtn"
                                  data-id="<?= $row['id']; ?>"
                                  data-name="<?= $row['teacher_name']; ?>"
                                  data-email="<?= $row['email']; ?>"
                                  data-contact="<?= $row['contact']; ?>"
                                  data-class="<?= $row['assigned_class']; ?>">
                                  <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="<?= $row['id']; ?>">
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

      <div class="modal fade" id="editTeacherModal" tabindex="-1" role="dialog" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editTeacherModalLabel"><i class="fa fa-edit"></i> Edit Teacher</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST" class="needs-validation" novalidate="">
              <input type="hidden" name="u_id" id="u_id">
              <div class="modal-body">
                <div class="form-group">
                  <label>Teacher Name</label>
                  <input type="text" name="u_name" id="u_name" class="form-control" required="">
                  <div class="invalid-feedback">Name is required.</div>
                </div>
                <div class="form-group">
                  <label>Email</label>
                  <input type="email" name="u_email" id="u_email" class="form-control" required="">
                  <div class="invalid-feedback">Valid email is required.</div>
                </div>
                <div class="form-group">
                  <label>Contact</label>
                  <input type="text" name="u_contact" id="u_contact" class="form-control" required="">
                  <div class="invalid-feedback">Contact is required.</div>
                </div>
                <div class="form-group">
                  <label>Class</label>
                  <select name="u_class" id="u_class" class="form-control select2" style="width:100%;">
                    <option value="">Select Class</option>
                    <?php foreach ($classes_dropdown as $cls): ?>
                      <option value="<?= htmlspecialchars($cls['class_name']) ?>"><?= htmlspecialchars($cls['class_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
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

      <div class="modal fade" id="deleteTeacherModal" tabindex="-1" role="dialog" aria-labelledby="deleteTeacherModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title" id="deleteTeacherModalLabel"><i class="fa fa-exclamation-triangle"></i> Confirm Delete</h5>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
              <input type="hidden" name="d_id" id="d_id">
              <div class="modal-body text-center">
                <p class="mb-1"><strong>Are you sure?</strong></p>
                <p class="text-muted small">This action cannot be undone.</p>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">No</button>
                <button type="submit" name="btn_delete" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Yes, Delete</button>
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
    // EDIT MODAL FILLING
    $(document).on('click', '.editBtn', function() {
      $('#u_id').val($(this).data('id'));
      $('#u_name').val($(this).data('name'));
      $('#u_email').val($(this).data('email'));
      $('#u_contact').val($(this).data('contact'));
      $('#u_class').val($(this).data('class')).trigger('change');
      $('#editTeacherModal').modal('show');
    });

    // DELETE MODAL FILLING
    $(document).on('click', '.deleteBtn', function() {
      $('#d_id').val($(this).data('id'));
      $('#deleteTeacherModal').modal('show');
    });

    // SWEETALERT LOGIC
    <?php if (isset($_SESSION['msg'])): ?>
      swal({
        title: "<?= $_SESSION['msg_title'] ?>",
        text: "<?= $_SESSION['msg'] ?>",
        icon: "<?= $_SESSION['msg_type'] ?>",
        timer: 2500,
        buttons: false
      });
      <?php
      unset($_SESSION['msg']);
      unset($_SESSION['msg_title']);
      unset($_SESSION['msg_type']);
      ?>
    <?php endif; ?>
  </script>
</body>

</html>