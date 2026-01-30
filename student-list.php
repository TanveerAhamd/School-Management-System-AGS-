<?php

/**
 * 1. DATABASE CONNECTION & AJAX HANDLERS
 */
require_once 'auth.php';

// --- AJAX Handler for Sections ---
if (isset($_GET['action']) && $_GET['action'] == 'fetch_sections') {
  $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
  $stmt->execute([$_GET['class_id'] ?? 0]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

/**
 * 2. SOFT DELETE LOGIC (Media Shift)
 */
if (isset($_GET['delete_id'])) {
  $id = $_GET['delete_id'];
  $stmt = $pdo->prepare("SELECT student_photo, cnic_doc, guardian_cnic_front, guardian_cnic_back, result_card_doc FROM students WHERE id = ?");
  $stmt->execute([$id]);
  $student = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($student) {
    $trashFolder = 'deleted_media/';
    if (!is_dir($trashFolder)) mkdir($trashFolder, 0777, true);
    foreach ($student as $key => $filePath) {
      if (!empty($filePath) && file_exists($filePath)) {
        // Filename with ID prefix to avoid overwriting same names
        $newPath = $trashFolder . $id . "_" . basename($filePath);
        if (rename($filePath, $newPath)) {
          $pdo->prepare("UPDATE students SET $key = ? WHERE id = ?")->execute([$newPath, $id]);
        }
      }
    }
    // Mark as is_deleted = 1 (Archived)
    $pdo->prepare("UPDATE students SET is_deleted = 1 WHERE id = ?")->execute([$id]);
  }
  header("Location: student-list.php?status=deleted");
  exit;
}

/**
 * 3. GLOBAL FETCH LOGIC (FETCHING ALL STATUSES)
 */
$classes = $pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC")->fetchAll();

$f_sess = $_GET['session_id'] ?? '';
$f_class = $_GET['class_id'] ?? '';
$f_sec = $_GET['section_id'] ?? '';
$f_stat = $_GET['student_status'] ?? 'All';

$where_clauses = ["1=1"]; // Default: Fetch everything
$params = [];

if (!empty($f_sess)) {
  $where_clauses[] = "s.session = ?";
  $params[] = $f_sess;
}
if (!empty($f_class)) {
  $where_clauses[] = "s.class_id = ?";
  $params[] = $f_class;
}
if (!empty($f_sec)) {
  $where_clauses[] = "s.section_id = ?";
  $params[] = $f_sec;
}

// Status Filtering Logic
if ($f_stat == 'Active') {
  $where_clauses[] = "s.is_deleted = 0 AND s.is_passout = 0 AND s.is_dropout = 0";
} elseif ($f_stat == 'Passout') {
  $where_clauses[] = "s.is_passout = 1";
} elseif ($f_stat == 'Dropout') {
  $where_clauses[] = "s.is_dropout = 1";
} elseif ($f_stat == 'Archived') {
  $where_clauses[] = "s.is_deleted = 1 AND s.is_passout = 0 AND s.is_dropout = 0";
}

$query = "SELECT s.*, c.class_name, sec.section_name 
          FROM students s
          LEFT JOIN classes c ON s.class_id = c.id
          LEFT JOIN sections sec ON s.section_id = sec.id
          WHERE " . implode(" AND ", $where_clauses) . "
          ORDER BY s.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Student List | AGS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <style>
    #student_list_card .st-img-circle {
      width: 35px;
      height: 35px;
      object-fit: cover;
      border-radius: 50%;
      border: 1px solid #ddd;
    }

    .badge-status {
      font-size: 9px;
      text-transform: uppercase;
      font-weight: 700;
      padding: 4px 8px;
    }

    .interest-badge {
      font-size: 8px;
      font-weight: 600;
      text-transform: capitalize;
      padding: 2px 6px;
      margin-right: 2px;
      margin-bottom: 2px;
      display: inline-block;
      border: 1px solid #eee;
    }
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

            <div class="row bg-title no-print">
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-body py-2 b-0 d-flex justify-content-between align-items-center">
                    <h5 class="page-title mb-0">Students List </h5>
                    <nav aria-label="breadcrumb">
                      <ol class="breadcrumb mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active">All</li>
                      </ol>
                    </nav>
                  </div>
                </div>
              </div>
            </div>

            <div class="row" id="student_list_card">
              <div class="col-12">
                <div class="card shadow-sm">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fa fa-users"></i>&nbsp;Filter Global Records</p>
                  </div>

                  <div class="card-body border-bottom no-print">
                    <form method="GET">
                      <div class="row">
                        <div class="col-md-2">
                          <label class="small font-weight-bold">Academic Session</label>
                          <select name="session_id" class="form-control select2">
                            <option value="">All Sessions</option>
                            <?php foreach ($sessions as $s) echo "<option value='{$s['id']}' " . ($f_sess == $s['id'] ? 'selected' : '') . ">{$s['session_name']}</option>"; ?>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="small font-weight-bold">Target Class</label>
                          <select name="class_id" id="filter_class" class="form-control select2">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $c) echo "<option value='{$c['id']}' " . ($f_class == $c['id'] ? 'selected' : '') . ">{$c['class_name']}</option>"; ?>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="small font-weight-bold">Section</label>
                          <select name="section_id" id="filter_section" class="form-control select2">
                            <option value="">All Sections</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="small font-weight-bold">Status Profile</label>
                          <select name="student_status" class="form-control select2">
                            <option value="All" <?= ($f_stat == 'All' ? 'selected' : '') ?>>All Registered Students</option>
                            <option value="Active" <?= ($f_stat == 'Active' ? 'selected' : '') ?>>Active Only</option>
                            <option value="Passout" <?= ($f_stat == 'Passout' ? 'selected' : '') ?>>Passout Only</option>
                            <option value="Dropout" <?= ($f_stat == 'Dropout' ? 'selected' : '') ?>>Dropout Only</option>
                            <option value="Archived" <?= ($f_stat == 'Archived' ? 'selected' : '') ?>>Deleted/Archived Only</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="d-block">&nbsp;</label>
                          <button type="submit" class="btn btn-primary btn-block shadow-sm"><i class="fa fa-search"></i> Fetch Global List</button>
                        </div>
                      </div>
                    </form>
                  </div>

                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExportImages" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Reg#</th>
                            <th>Image</th>
                            <th>Student Name</th>
                            <th>Class (Sec)</th>
                            <th>Interests</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $count = 1;
                          foreach ($students as $row):
                            $photo = (!empty($row['student_photo']) && file_exists($row['student_photo'])) ? $row['student_photo'] : 'assets/img/userdummypic.png';

                            // PRIORITY STATUS LOGIC
                            if ($row['is_passout'] == 1) {
                              $status = '<span class="badge badge-success badge-status">Passout</span>';
                            } elseif ($row['is_dropout'] == 1) {
                              $status = '<span class="badge badge-danger badge-status">Dropout</span>';
                            } elseif ($row['is_deleted'] == 1) {
                              $status = '<span class="badge badge-secondary badge-status">Archived</span>';
                            } else {
                              $status = '<span class="badge badge-primary badge-status">Active</span>';
                            }

                            // Editable check: Only if Active (not deleted, not passout, not dropout)
                            $is_active = ($row['is_deleted'] == 0 && $row['is_passout'] == 0 && $row['is_dropout'] == 0);
                          ?>
                            <tr>
                              <td><?= $count++ ?></td>
                              <td class="font-weight-bold "><?= $row['reg_no'] ?></td>
                              <td><img src="<?= $photo ?>" class="st-img-circle"></td>
                              <td class="text-uppercase small font-weight-bold"><?= $row['student_name'] ?></td>
                              <td><?= $row['class_name'] ?> (<?= $row['section_name'] ?>)</td>

                              <td>
                                <?php
                                if (!empty($row['interests'])) {
                                  $interests = explode(',', $row['interests']);
                                  foreach ($interests as $i) echo '<span class="badge badge-light interest-badge">' . trim($i) . '</span>';
                                } else {
                                  echo '<small class="text-muted">-</small>';
                                }
                                ?>
                              </td>

                              <td><?= $status ?></td>
                              <td>
                                <div class="d-flex align-items-center gap-1">
                                  <!-- View Detail: Always enabled -->
                                  <a href="student-detail-page.php?id=<?= $row['id'] ?>" class="btn btn-success btn-circle btn-xs" title="View Detail"><i class="fa fa-eye"></i></a>

                                  <?php if ($is_active): ?>
                                    <!-- Edit & Archive enabled only for ACTIVE students -->
                                    <a href="student-edit-page.php?id=<?= $row['id'] ?>" class="btn btn-info btn-circle btn-xs" title="Edit Record"><i class="fas fa-pencil-alt"></i></a>
                                    <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-danger btn-circle btn-xs" title="Archive"><i class="fa fa-times"></i></a>
                                  <?php else: ?>
                                    <!-- LOCK icons for Edit & Archive if status is not Active -->
                                    <button class="btn btn-light btn-circle btn-xs" disabled title="Edit Locked"><i class="fa fa-lock"></i></button>
                                    <button class="btn btn-light btn-circle btn-xs" disabled title="Status Locked"><i class="fa fa-lock"></i></button>
                                  <?php endif; ?>
                                </div>
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

      $('#tableExportImages').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'print', 'pdf']
      });

      // Dynamic Section Loader
      $('#filter_class').on('change', function() {
        var cid = $(this).val();
        if (cid) {
          $.getJSON('student-list.php?action=fetch_sections&class_id=' + cid, function(data) {
            var h = '<option value="">All Sections</option>';
            data.forEach(d => {
              let sel = (d.id == "<?= @$_GET['section_id'] ?>") ? 'selected' : '';
              h += `<option value="${d.id}" ${sel}>${d.section_name}</option>`;
            });
            $('#filter_section').html(h);
          });
        }
      });
      if ($('#filter_class').val()) $('#filter_class').trigger('change');
    });

    function confirmDelete(id) {
      Swal.fire({
        title: 'Archive Student?',
        text: "This student will be moved to archived records and media will be shifted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, Archive!'
      }).then((result) => {
        if (result.isConfirmed) window.location.href = 'student-list.php?delete_id=' + id;
      });
    }
  </script>
</body>

</html>