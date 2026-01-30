<?php
require_once 'auth.php';

// Session check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

// Filter Logic
$filter_class = isset($_GET['class_id']) ? $_GET['class_id'] : 'all';

// ==========================================
// 1. DATABASE OPERATIONS (ADD, UPDATE, DELETE)
// ==========================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- ADD SUBJECT ---
    if (isset($_POST['btn_save'])) {
        $subject_name = trim($_POST['sub_name']);
        $class_id = $_POST['class_id'];
        $teacher_id = $_POST['teacher_id'];

        if (!empty($subject_name) && !empty($class_id)) {
            $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, class_id, teacher_id) VALUES (?, ?, ?)");
            if ($stmt->execute([$subject_name, $class_id, $teacher_id])) {
                $_SESSION['msg'] = "Subject successfully add ho gaya!";
                $_SESSION['msg_title'] = "Success!";
                $_SESSION['msg_type'] = "success";
            }
        }
        header("Location: $current_page" . ($filter_class != 'all' ? "?class_id=$filter_class" : ""));
        exit();
    }

    // --- UPDATE SUBJECT ---
    if (isset($_POST['btn_update'])) {
        $id = $_POST['u_id'];
        $subject_name = trim($_POST['u_name']);
        $class_id = $_POST['u_class'];
        $teacher_id = $_POST['u_teacher'];

        $stmt = $pdo->prepare("UPDATE subjects SET subject_name = ?, class_id = ?, teacher_id = ? WHERE id = ?");
        if ($stmt->execute([$subject_name, $class_id, $teacher_id, $id])) {
            $_SESSION['msg'] = "Subject update ho gaya!";
            $_SESSION['msg_title'] = "Updated!";
            $_SESSION['msg_type'] = "success";
        }
        header("Location: $current_page" . ($filter_class != 'all' ? "?class_id=$filter_class" : ""));
        exit();
    }

    // --- DELETE SUBJECT ---
    if (isset($_POST['btn_delete'])) {
        $id = $_POST['d_id'];
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['msg'] = "Subject delete kar diya gaya!";
            $_SESSION['msg_title'] = "Deleted!";
            $_SESSION['msg_type'] = "success";
        }
        header("Location: $current_page" . ($filter_class != 'all' ? "?class_id=$filter_class" : ""));
        exit();
    }
}

// ==========================================
// 2. FETCH DATA (Dynamic Dropdowns & Table)
// ==========================================

// Fetch Classes for dropdowns
$all_classes = $pdo->query("SELECT * FROM classes ORDER BY CAST(numeric_name AS UNSIGNED) ASC")->fetchAll();

// Fetch Teachers for dropdowns
$all_teachers = $pdo->query("SELECT id, teacher_name FROM teachers ORDER BY teacher_name ASC")->fetchAll();

// Fetch Subjects with Join
$sql = "SELECT subjects.*, classes.class_name, teachers.teacher_name 
        FROM subjects 
        INNER JOIN classes ON subjects.class_id = classes.id 
        LEFT JOIN teachers ON subjects.teacher_id = teachers.id";

if ($filter_class !== 'all') {
    $sql .= " WHERE subjects.class_id = " . intval($filter_class);
}
$sql .= " ORDER BY subjects.id DESC";
$subjects_list = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Manage Subjects | AGS Lodhran</title>
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
                        <h5 class="page-title mb-0">Manage Subject</h5>
                      </div>
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                            <li class="breadcrumb-item active">Subject</li>
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
                    <p class="mb-0 font-weight-bold"><i class="fa fa-plus"></i>&nbsp;Add Subject</p>
                  </div>
                  <form method="POST" class="needs-validation" novalidate="">
                    <div class="card-body">
                      <div class="form-group">
                        <label>Subject Name</label>
                        <input type="text" name="sub_name" class="form-control" required>
                        <div class="invalid-feedback">What's Subject name?</div>
                      </div>
                      <div class="form-group">
                        <label>Class</label>
                        <select name="class_id" class="form-control select2" required>
                          <option value="">Select Class</option>
                          <?php foreach($all_classes as $cls): ?>
                            <option value="<?= $cls['id'] ?>"><?= $cls['class_name'] ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Teacher</label>
                        <select name="teacher_id" class="form-control select2" required>
                          <option value="">Select Teacher</option>
                          <?php foreach($all_teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= $t['teacher_name'] ?></option>
                          <?php endforeach; ?>
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
                    <p class="mb-0 font-weight-bold transform-uppercase"> <i class="fa fa-list"></i>&nbsp;List Subject</p>
                  </div>
                  <div class="card-body">
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Filter by Class:</label>
                        <select class="form-control select2" id="classFilter">
                          <option value="all" <?= ($filter_class == 'all') ? 'selected' : '' ?>>All Classes (Show All Subjects)</option>
                          <?php foreach($all_classes as $cls): ?>
                            <option value="<?= $cls['id'] ?>" <?= ($filter_class == $cls['id']) ? 'selected' : '' ?>>
                                <?= $cls['class_name'] ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Subject Name</th>
                            <th>Class</th>
                            <th>Teacher</th>
                            <th>Options</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $count = 1; foreach($subjects_list as $row): ?>
                          <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td><span class="badge badge-dark"><?= htmlspecialchars($row['class_name']) ?></span></td>
                            <td><?= htmlspecialchars($row['teacher_name'] ?? 'Not Assigned') ?></td>
                            <td>
                              <button class="btn btn-sm btn-primary editBtn" 
                                data-id="<?= $row['id'] ?>" 
                                data-name="<?= $row['subject_name'] ?>" 
                                data-class="<?= $row['class_id'] ?>" 
                                data-teacher="<?= $row['teacher_id'] ?>">
                                <i class="fa fa-edit"></i>
                              </button>
                              <button class="btn btn-sm btn-danger deleteBtn" data-id="<?= $row['id'] ?>">
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

      <div class="modal fade" id="editSubjectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Subject</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
              <input type="hidden" name="u_id" id="u_id">
              <div class="modal-body">
                <div class="form-group">
                  <label>Subject Name</label>
                  <input type="text" name="u_name" id="u_name" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Class</label>
                  <select name="u_class" id="u_class" class="form-control select2" style="width:100%" required>
                    <?php foreach($all_classes as $cls): ?>
                        <option value="<?= $cls['id'] ?>"><?= $cls['class_name'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Teacher</label>
                  <select name="u_teacher" id="u_teacher" class="form-control select2" style="width:100%" required>
                    <?php foreach($all_teachers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= $t['teacher_name'] ?></option>
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

      <div class="modal fade" id="deleteSubjectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title">Confirm Delete</h5>
              <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST">
                <input type="hidden" name="d_id" id="d_id">
                <div class="modal-body text-center">
                  <p>Are you sure you want to delete this subject?</p>
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
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.flash.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>
  <script src="assets/js/page/datatables.js"></script>
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  <script src="assets/js/scripts.js"></script>

  <script>
    // FIX: Dynamic URL logic for Filter
    $('#classFilter').on('change', function() {
        var classId = $(this).val();
        // window.location.pathname ensures folder spaces don't break the path
        window.location.href = window.location.pathname + "?class_id=" + classId;
    });

    // Edit Modal filling
    $(document).on('click', '.editBtn', function() {
        $('#u_id').val($(this).data('id'));
        $('#u_name').val($(this).data('name'));
        $('#u_class').val($(this).data('class')).trigger('change');
        $('#u_teacher').val($(this).data('teacher')).trigger('change');
        $('#editSubjectModal').modal('show');
    });

    // Delete Modal filling
    $(document).on('click', '.deleteBtn', function() {
        $('#d_id').val($(this).data('id'));
        $('#deleteSubjectModal').modal('show');
    });

    // SweetAlert
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