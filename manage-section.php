<?php
require_once 'auth.php';

// 1. Session & Page Security
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);

// 2. Filter Logic (Capsule Click)
$filter_class = isset($_GET['class_id']) ? $_GET['class_id'] : 'all';

// ==========================================
// 3. DATABASE OPERATIONS (ADD, UPDATE, DELETE)
// ==========================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // --- INSERT SECTION ---
  if (isset($_POST['btn_save'])) {
    $s_name = trim($_POST['s_name']);
    $s_nick = trim($_POST['s_nick']);
    $class_id = $_POST['class_id'];
    $teacher_id = $_POST['teacher_id'];

    if (!empty($s_name) && !empty($class_id)) {
      $stmt = $pdo->prepare("INSERT INTO sections (section_name, nick_name, class_id, teacher_id) VALUES (?, ?, ?, ?)");
      if ($stmt->execute([$s_name, $s_nick, $class_id, $teacher_id])) {
        $_SESSION['msg'] = "Section successfully add ho gaya!";
        $_SESSION['msg_title'] = "Success!";
        $_SESSION['msg_type'] = "success";
      }
    }
    header("Location: $current_page");
    exit();
  }

  // --- UPDATE SECTION ---
  if (isset($_POST['btn_update'])) {
    $id = $_POST['u_id'];
    $s_name = trim($_POST['u_name']);
    $s_nick = trim($_POST['u_nick']);
    $class_id = $_POST['u_class'];
    $teacher_id = $_POST['u_teacher'];

    $stmt = $pdo->prepare("UPDATE sections SET section_name=?, nick_name=?, class_id=?, teacher_id=? WHERE id=?");
    if ($stmt->execute([$s_name, $s_nick, $class_id, $teacher_id, $id])) {
      $_SESSION['msg'] = "Section update ho gaya!";
      $_SESSION['msg_title'] = "Updated!";
      $_SESSION['msg_type'] = "success";
    }
    header("Location: $current_page");
    exit();
  }

  // --- DELETE SECTION ---
  if (isset($_POST['btn_delete'])) {
    $id = $_POST['d_id'];
    $stmt = $pdo->prepare("DELETE FROM sections WHERE id = ?");
    if ($stmt->execute([$id])) {
      $_SESSION['msg'] = "Section delete kar diya gaya!";
      $_SESSION['msg_title'] = "Deleted!";
      $_SESSION['msg_type'] = "success";
    }
    header("Location: $current_page");
    exit();
  }
}

// ==========================================
// 4. FETCH DATA
// ==========================================
$all_classes = $pdo->query("SELECT * FROM classes ORDER BY CAST(numeric_name AS UNSIGNED) ASC")->fetchAll();
$all_teachers = $pdo->query("SELECT id, teacher_name FROM teachers ORDER BY teacher_name ASC")->fetchAll();

$sql = "SELECT sections.*, classes.class_name, teachers.teacher_name 
        FROM sections 
        INNER JOIN classes ON sections.class_id = classes.id 
        LEFT JOIN teachers ON sections.teacher_id = teachers.id";

if ($filter_class !== 'all') {
  $sql .= " WHERE sections.class_id = " . intval($filter_class);
}
$sql .= " ORDER BY sections.id DESC";
$sections_list = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Manage Section| AGHS Lodhran</title>
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
                  <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                      <h5 class="page-title mb-0">Manage Section</h5>
                      <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 bg-transparent p-0">
                          <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                          <li class="breadcrumb-item active">Section</li>
                        </ol>
                      </nav>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-4">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fa fa-plus"></i>&nbsp;Add Section</p>
                  </div>
                  <form method="POST" class="needs-validation" novalidate>
                    <div class="card-body">
                      <div class="form-group">
                        <label>Section Name</label>
                        <input type="text" name="s_name" class="form-control" required>
                      </div>
                      <div class="form-group">
                        <label>Section Nick Name</label>
                        <input type="text" name="s_nick" class="form-control" required>
                      </div>
                      <div class="form-group">
                        <label>Class</label>
                        <select name="class_id" class="form-control select2" required>
                          <option value="">Select Class</option>
                          <?php foreach ($all_classes as $class): ?>
                            <option value="<?= $class['id'] ?>"><?= $class['class_name'] ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Teacher</label>
                        <select name="teacher_id" class="form-control select2" required>
                          <option value="">Select Teacher</option>
                          <?php foreach ($all_teachers as $teacher): ?>
                            <option value="<?= $teacher['id'] ?>"><?= $teacher['teacher_name'] ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="card-footer text-center">
                      <button type="submit" name="btn_save" class="btn btn-info btn-block btn-sm">Save</button>
                    </div>
                  </form>
                </div>
              </div>

              <div class="col-sm-8">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fa fa-list"></i>&nbsp;List Section</p>
                  </div>
                  <div class="card-body">

                    <div class="badges mb-3">
                      <a href="manage-section.php?class_id=all" class="badge <?= ($filter_class == 'all') ? 'badge-primary' : 'badge-dark' ?>">All</a>
                      <?php foreach ($all_classes as $c): ?>
                        <a href="manage-section.php?class_id=<?= $c['id'] ?>" class="badge badge-info <?= ($filter_class == $c['id']) ? 'border border-dark' : '' ?>">
                          Class: <?= $c['class_name'] ?>
                        </a>
                      <?php endforeach; ?>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Section</th>
                            <th>Nickname</th>
                            <th>Class</th>
                            <th>Teacher</th>
                            <th>Options</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $count = 1;
                          foreach ($sections_list as $row): ?>
                            <tr>
                              <td><?= $count++ ?></td>
                              <td><?= $row['section_name'] ?></td>
                              <td><?= $row['nick_name'] ?></td>
                              <td><?= $row['class_name'] ?></td>
                              <td><?= $row['teacher_name'] ?></td>
                              <td>
                                <button class="btn btn-sm btn-primary editBtn" data-id="<?= $row['id'] ?>" data-name="<?= $row['section_name'] ?>" data-nick="<?= $row['nick_name'] ?>" data-class="<?= $row['class_id'] ?>" data-teacher="<?= $row['teacher_id'] ?>"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="<?= $row['id'] ?>"><i class="fa fa-trash"></i></button>
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

      <div class="modal fade" id="editSectionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Section</h5><button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
              <input type="hidden" name="u_id" id="u_id">
              <div class="modal-body">
                <div class="form-group"><label>Section Name</label><input type="text" name="u_name" id="u_name" class="form-control" required></div>
                <div class="form-group"><label>Nick Name</label><input type="text" name="u_nick" id="u_nick" class="form-control" required></div>
                <div class="form-group">
                  <label>Class</label>
                  <select name="u_class" id="u_class" class="form-control select2" style="width:100%"><?php foreach ($all_classes as $class) {
                                                                                                        echo "<option value='" . $class['id'] . "'>" . $class['class_name'] . "</option>";
                                                                                                      } ?></select>
                </div>
                <div class="form-group">
                  <label>Teacher</label>
                  <select name="u_teacher" id="u_teacher" class="form-control select2" style="width:100%"><?php foreach ($all_teachers as $teacher) {
                                                                                                            echo "<option value='" . $teacher['id'] . "'>" . $teacher['teacher_name'] . "</option>";
                                                                                                          } ?></select>
                </div>
              </div>
              <div class="modal-footer"><button type="submit" name="btn_update" class="btn btn-info">Update</button></div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade" id="deleteSectionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
          <div class="modal-content">
            <form method="POST">
              <input type="hidden" name="d_id" id="d_id">
              <div class="modal-body text-center">
                <h6>Delete this record?</h6>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                <button type="submit" name="btn_delete" class="btn btn-danger">Yes, Delete</button>
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
    $(document).on('click', '.editBtn', function() {
      $('#u_id').val($(this).data('id'));
      $('#u_name').val($(this).data('name'));
      $('#u_nick').val($(this).data('nick'));
      $('#u_class').val($(this).data('class')).trigger('change');
      $('#u_teacher').val($(this).data('teacher')).trigger('change');
      $('#editSectionModal').modal('show');
    });

    $(document).on('click', '.deleteBtn', function() {
      $('#d_id').val($(this).data('id'));
      $('#deleteSectionModal').modal('show');
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