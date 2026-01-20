<?php
require_once 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ==========================================
// 1. AJAX HANDLER: Fetch Subjects (Fixed)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'fetch_subjects') {
  $class_id = $_POST['class_id'];
  $group_id = (isset($_POST['group_id']) && !empty($_POST['group_id'])) ? $_POST['group_id'] : null;

  $selected_subs = [];
  if ($group_id) {
    $stmt_check = $pdo->prepare("SELECT subject_id FROM subject_group_items WHERE group_id = ?");
    $stmt_check->execute([$group_id]);
    $selected_subs = $stmt_check->fetchAll(PDO::FETCH_COLUMN);
  }

  $stmt = $pdo->prepare("SELECT id, subject_name FROM subjects WHERE class_id = ? ORDER BY subject_name ASC");
  $stmt->execute([$class_id]);
  $subjects = $stmt->fetchAll();

  if ($subjects) {
    foreach ($subjects as $s) {
      $sel = in_array($s['id'], $selected_subs) ? "selected" : "";
      echo "<option value='" . $s['id'] . "' $sel>" . $s['subject_name'] . "</option>";
    }
  } else {
    echo "<option value=''>Is class mein koi subject nahi mila</option>";
  }
  exit;
}

// ==========================================
// 2. SAVE / UPDATE LOGIC
// ==========================================
if (isset($_POST['btn_save'])) {
  $group_name = trim($_POST['group_name']);
  $class_id = $_POST['class_id'];
  $subject_ids = isset($_POST['subject_ids']) ? $_POST['subject_ids'] : [];
  $update_id = isset($_POST['update_id']) && !empty($_POST['update_id']) ? $_POST['update_id'] : null;

  if (!empty($group_name) && !empty($class_id) && !empty($subject_ids)) {
    try {
      $pdo->beginTransaction();

      if ($update_id) {
        $stmt = $pdo->prepare("UPDATE subject_groups SET group_name = ?, class_id = ? WHERE id = ?");
        $stmt->execute([$group_name, $class_id, $update_id]);

        // Purani mapping delete karna zaroori hai
        $pdo->prepare("DELETE FROM subject_group_items WHERE group_id = ?")->execute([$update_id]);
        $group_id = $update_id;
        $_SESSION['swal_msg'] = ["title" => "Updated!", "text" => "Group Updated Successfully", "type" => "success"];
      } else {
        $stmt = $pdo->prepare("INSERT INTO subject_groups (group_name, class_id) VALUES (?, ?)");
        $stmt->execute([$group_name, $class_id]);
        $group_id = $pdo->lastInsertId();
        $_SESSION['swal_msg'] = ["title" => "Saved!", "text" => "Group Created Successfully", "type" => "success"];
      }

      // Nayi mapping insert karna
      $stmt_item = $pdo->prepare("INSERT INTO subject_group_items (group_id, subject_id) VALUES (?, ?)");
      foreach ($subject_ids as $sub_id) {
        if (!empty($sub_id)) { // Integrity Check
          $stmt_item->execute([$group_id, $sub_id]);
        }
      }
      $pdo->commit();
    } catch (Exception $e) {
      $pdo->rollBack();
      $_SESSION['swal_msg'] = ["title" => "SQL Error!", "text" => "Check if subjects exist for this class.", "type" => "error"];
    }
  }
  header("Location: subject-group.php");
  exit();
}

// ==========================================
// 3. DELETE LOGIC
// ==========================================
if (isset($_GET['del'])) {
  $del_id = $_GET['del'];
  $pdo->prepare("DELETE FROM subject_group_items WHERE group_id = ?")->execute([$del_id]);
  $pdo->prepare("DELETE FROM subject_groups WHERE id = ?")->execute([$del_id]);
  $_SESSION['swal_msg'] = ["title" => "Deleted!", "text" => "Group has been removed", "type" => "success"];
  header("Location: subject-group.php");
  exit();
}

$all_classes = $pdo->query("SELECT * FROM classes ORDER BY id ASC")->fetchAll();
$groups_list = $pdo->query("SELECT sg.*, c.class_name FROM subject_groups sg JOIN classes c ON sg.class_id = c.id ORDER BY sg.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Manage Subject Group</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/bundles/select2/dist/css/select2.min.css">
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
      <div class="main-sidebar sidebar-style-2"><?php include 'include/asidebar.php'; ?></div>

      <div class="main-content">
        <section class="section">
          <div class="section-body">

            <div class="row bg-title">
              <div class="col-12">
                <div class="card mb-3 py-2">
                  <div class="card-body d-flex align-items-center justify-content-between py-1">
                    <h5 class="page-title mb-0">Manage Subject Group</h5>
                    <nav aria-label="breadcrumb">
                      <ol class="breadcrumb mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Group Subject</li>
                      </ol>
                    </nav>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-4">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fa fa-plus"></i>&nbsp;Add Subject Group</p>
                  </div>
                  <form method="POST" action="subject-group.php" class="needs-validation" novalidate="">
                    <div class="card-body">
                      <div class="form-group">
                        <label>Group Name</label>
                        <input type="text" name="group_name" class="form-control" placeholder="Enter Name" required>
                      </div>
                      <div class="form-group">
                        <label>Class</label>
                        <select name="class_id" class="form-control select2 class_dropdown" style="width:100%" required>
                          <option value="">Select Class</option>
                          <?php foreach ($all_classes as $cls): ?>
                            <option value="<?= $cls['id'] ?>"><?= $cls['class_name'] ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Subjects (Multiple)</label>
                        <select name="subject_ids[]" class="form-control select2 subject_dropdown" multiple="multiple" style="width:100%" required>
                        </select>
                      </div>
                    </div>
                    <div class="card-footer">
                      <button type="submit" name="btn_save" class="btn btn-info btn-block btn-rounded">
                        <i class="fa fa-save"></i>&nbsp;Save Group
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <div class="col-sm-8">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fa fa-list"></i>&nbsp;Subject Groups List</p>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Group Name</th>
                            <th>Class</th>
                            <th>Subjects</th>
                            <th class="text-center">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($groups_list as $index => $row):
                            $stmt = $pdo->prepare("SELECT s.subject_name FROM subject_group_items sgi JOIN subjects s ON sgi.subject_id = s.id WHERE sgi.group_id = ?");
                            $stmt->execute([$row['id']]);
                            $subs = $stmt->fetchAll(PDO::FETCH_COLUMN);
                          ?>
                            <tr>
                              <td><?= $index + 1 ?></td>
                              <td><strong><?= $row['group_name'] ?></strong></td>
                              <td><span class="badge badge-info"><?= $row['class_name'] ?></span></td>
                              <td><?= implode(", ", $subs) ?></td>
                              <td class="text-center">
                                <button class="btn btn-primary btn-sm btn-edit"
                                  data-id="<?= $row['id'] ?>"
                                  data-name="<?= $row['group_name'] ?>"
                                  data-class="<?= $row['class_id'] ?>">
                                  <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="<?= $row['id'] ?>">
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
    </div>
  </div>

  <div class="modal fade" id="editModal" role="dialog">
    <div class="modal-dialog">
      <form method="POST" action="subject-group.php" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Subject Group</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="update_id" id="edit_id">
          <div class="form-group">
            <label>Group Name</label>
            <input type="text" name="group_name" id="edit_name" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Class</label>
            <select name="class_id" id="edit_class" class="form-control select2 class_dropdown" style="width:100%" required>
              <?php foreach ($all_classes as $cls): ?>
                <option value="<?= $cls['id'] ?>"><?= $cls['class_name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Subjects</label>
            <select name="subject_ids[]" id="edit_subjects" class="form-control select2 subject_dropdown" multiple="multiple" style="width:100%" required>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="btn_save" class="btn btn-info">Update Changes</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>
  <script src="assets/bundles/select2/dist/js/select2.full.min.js"></script>
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  <script src="assets/js/page/datatables.js"></script>
  <script src="assets/js/scripts.js"></script>

  <script>
    $(document).ready(function() {
      $('.select2').select2();

      // PHP SweetAlert Show
      <?php if (isset($_SESSION['swal_msg'])): ?>
        swal("<?= $_SESSION['swal_msg']['title'] ?>", "<?= $_SESSION['swal_msg']['text'] ?>", "<?= $_SESSION['swal_msg']['type'] ?>");
        <?php unset($_SESSION['swal_msg']); ?>
      <?php endif; ?>

      // Dynamic Subject Loading
      $(document).on('change', '.class_dropdown', function() {
        var class_id = $(this).val();
        var targetSelect = $(this).closest('form').find('.subject_dropdown');
        var gid = $('#edit_id').val();

        if (class_id) {
          $.ajax({
            url: 'subject-group.php',
            type: 'POST',
            data: {
              action: 'fetch_subjects',
              class_id: class_id,
              group_id: gid
            },
            success: function(response) {
              targetSelect.empty().append(response).trigger('change.select2');
            }
          });
        }
      });

      // Edit Button Logic
      $('.btn-edit').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var cid = $(this).data('class');

        $('#edit_id').val(id);
        $('#edit_name').val(name);
        $('#edit_class').val(cid).trigger('change');
        $('#editModal').modal('show');
      });

      // SweetAlert Delete Logic
      $('.btn-delete').on('click', function() {
        var id = $(this).data('id');
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this group!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              window.location.href = "subject-group.php?del=" + id;
            }
          });
      });
    });
  </script>
</body>

</html>