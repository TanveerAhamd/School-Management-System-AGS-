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
    
    // --- ADD CLASS ---
    if (isset($_POST['btn_save'])) {
        $c_name = trim($_POST['c_name']);
        $c_numeric = trim($_POST['c_numeric']);
        $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : NULL;

        if (!empty($c_name) && !empty($c_numeric)) {
            $stmt = $pdo->prepare("INSERT INTO classes (class_name, numeric_name, teacher_id) VALUES (?, ?, ?)");
            if ($stmt->execute([$c_name, $c_numeric, $teacher_id])) {
                $_SESSION['msg'] = "Class successfully add ho gayi!";
                $_SESSION['msg_title'] = "Success!";
                $_SESSION['msg_type'] = "success";
            }
        }
        header("Location: $current_page");
        exit();
    }

    // --- UPDATE CLASS ---
    if (isset($_POST['btn_update'])) {
        $id = $_POST['u_id'];
        $c_name = trim($_POST['u_name']);
        $c_numeric = trim($_POST['u_numeric']);
        $teacher_id = !empty($_POST['u_teacher']) ? $_POST['u_teacher'] : NULL;

        $stmt = $pdo->prepare("UPDATE classes SET class_name = ?, numeric_name = ?, teacher_id = ? WHERE id = ?");
        if ($stmt->execute([$c_name, $c_numeric, $teacher_id, $id])) {
            $_SESSION['msg'] = "Class record update ho gaya!";
            $_SESSION['msg_title'] = "Updated!";
            $_SESSION['msg_type'] = "success";
        }
        header("Location: $current_page");
        exit();
    }

    // --- DELETE CLASS ---
    if (isset($_POST['btn_delete'])) {
        $id = $_POST['d_id'];
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['msg'] = "Class delete kar di gayi!";
            $_SESSION['msg_title'] = "Deleted!";
            $_SESSION['msg_type'] = "success";
        }
        header("Location: $current_page");
        exit();
    }
}

// ==========================================
// 2. FETCH DATA (Teachers & Classes)
// ==========================================
// Teachers for Dropdown
$stmt_t = $pdo->query("SELECT id, teacher_name FROM teachers ORDER BY teacher_name ASC");
$teachers = $stmt_t->fetchAll();

// Classes for Table (with Teacher Name Join)
$stmt_c = $pdo->query("SELECT classes.*, teachers.teacher_name 
                       FROM classes 
                       LEFT JOIN teachers ON classes.teacher_id = teachers.id 
                       ORDER BY classes.id DESC");
$classes_list = $stmt_c->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Manage Class| AGHS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="icon" href="assets/img/favicon.png">
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
                        <h5 class="page-title mb-0">Manage Class</h5>
                      </div>
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="#"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                            <li class="breadcrumb-item active"><i class="fas fa-list"></i> Class</li>
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
                    <p class="mb-0 font-weight-bold"><i class="fa fa-plus"></i>&nbsp;Add Class</p>
                  </div>
                  <form method="POST" class="needs-validation" novalidate="">
                    <div class="card-body">
                      <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="c_name" class="form-control" placeholder="e.g. Class One" required>
                        <div class="invalid-feedback">What's Class name?</div>
                      </div>
                      <div class="form-group">
                        <label>Name Numeric</label>
                        <input type="text" name="c_numeric" class="form-control" placeholder="e.g. 1" required>
                        <div class="invalid-feedback">What's Class Numeric name?</div>
                      </div>
                      <div class="form-group">
                        <label>Teacher (Optional)</label>
                        <select name="teacher_id" class="form-control select2">
                          <option value="">Select Teacher</option>
                          <?php foreach($teachers as $t) { ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['teacher_name']) ?></option>
                          <?php } ?>
                        </select>
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
                    <p class="mb-0 font-weight-bold transform-uppercase"> <i class="fa fa-list"></i>&nbsp;List Class</p>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Class Name</th>
                            <th>Numeric Name</th>
                            <th>Teacher</th>
                            <th>Options</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $count = 1; foreach($classes_list as $row) { ?>
                          <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                            <td><?= htmlspecialchars($row['numeric_name']) ?></td>
                            <td><?= $row['teacher_name'] ? htmlspecialchars($row['teacher_name']) : '<span class="badge badge-warning">Not Assigned</span>' ?></td>
                            <td>
                              <button class="btn btn-sm btn-primary editBtn" 
                                      data-id="<?= $row['id'] ?>" 
                                      data-name="<?= $row['class_name'] ?>" 
                                      data-num="<?= $row['numeric_name'] ?>" 
                                      data-teacher="<?= $row['teacher_id'] ?>">
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

      <div class="modal fade" id="editClassModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Class</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" class="needs-validation" novalidate="">
              <input type="hidden" name="u_id" id="u_id">
              <div class="modal-body">
                <div class="form-group">
                  <label>Class Name</label>
                  <input type="text" name="u_name" id="u_name" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Numeric Name</label>
                  <input type="text" name="u_numeric" id="u_numeric" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Teacher</label>
                  <select name="u_teacher" id="u_teacher" class="form-control select2">
                    <option value="">Select Teacher</option>
                    <?php foreach($teachers as $t) { ?>
                      <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['teacher_name']) ?></option>
                    <?php } ?>
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

      <div class="modal fade" id="deleteClassModal" tabindex="-1" role="dialog" aria-hidden="true">
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
        $('#u_numeric').val($(this).data('num'));
        $('#u_teacher').val($(this).data('teacher')).trigger('change');
        $('#editClassModal').modal('show');
    });

    $(document).on('click', '.deleteBtn', function() {
        $('#d_id').val($(this).data('id'));
        $('#deleteClassModal').modal('show');
    });

    <?php if(isset($_SESSION['msg'])): ?>
      swal({
        title: "<?= $_SESSION['msg_title'] ?>",
        text: "<?= $_SESSION['msg'] ?>",
        icon: "<?= $_SESSION['msg_type'] ?>",
        timer: 2000,
        buttons: false
      });
      <?php unset($_SESSION['msg']); unset($_SESSION['msg_title']); unset($_SESSION['msg_type']); ?>
    <?php endif; ?>
  </script>
</body>
</html>