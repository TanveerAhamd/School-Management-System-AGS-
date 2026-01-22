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
 * 2. SOFT DELETE & FILE MIGRATION LOGIC
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
                $fileName = basename($filePath);
                $newPath = $trashFolder . $fileName;
                if (rename($filePath, $newPath)) {
                    $pdo->prepare("UPDATE students SET $key = ? WHERE id = ?")->execute([$newPath, $id]);
                }
            }
        }
        $pdo->prepare("UPDATE students SET is_deleted = 1 WHERE id = ?")->execute([$id]);
    }
    header("Location: student-list.php?status=deleted");
    exit;
}

/**
 * 3. GLOBAL FETCH LOGIC
 */
$classes = $pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC")->fetchAll();

$f_sess = $_GET['session_id'] ?? '';
$f_class = $_GET['class_id'] ?? '';
$f_sec = $_GET['section_id'] ?? '';
$f_stat = $_GET['student_status'] ?? 'All';

$where_clauses = ["1=1"]; 
$params = [];

if (!empty($f_sess)) { $where_clauses[] = "s.session = ?"; $params[] = $f_sess; }
if (!empty($f_class)) { $where_clauses[] = "s.class_id = ?"; $params[] = $f_class; }
if (!empty($f_sec)) { $where_clauses[] = "s.section_id = ?"; $params[] = $f_sec; }

// Status Filtering
if ($f_stat == 'Active') {
    $where_clauses[] = "s.is_deleted = 0 AND s.is_passout = 0 AND s.is_dropout = 0";
} elseif ($f_stat == 'Passout') {
    $where_clauses[] = "s.is_passout = 1";
} elseif ($f_stat == 'Dropout') {
    $where_clauses[] = "s.is_dropout = 1";
} elseif ($f_stat == 'Archived') {
    $where_clauses[] = "s.is_deleted = 1";
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
  <title>Global Registry | AGS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />
  <style>
    /* Scoped UI Classes to protect Sidebar */
    #student_list_card .st-img-circle { width: 35px; height: 35px; object-fit: cover; border-radius: 50%; border: 1px solid #ddd; }
    .badge-status { font-size: 10px; text-transform: uppercase; font-weight: 700; padding: 5px 10px; }
  </style>
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
                <div class="card mb-3">
                  <div class="card-body py-2 b-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                      <h5 class="page-title mb-0">Global Student Registry</h5>
                      <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 bg-transparent p-0">
                          <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                          <li class="breadcrumb-item active">Student Registry</li>
                        </ol>
                      </nav>
                    </div>
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

                  <div class="card-body border-bottom ">
                    <form method="GET" action="student-list.php">
                      <div class="row">
                        <div class="col-md-2">
                          <select name="session_id" class="form-control select2">
                            <option value="">All Sessions</option>
                            <?php foreach ($sessions as $s) echo "<option value='{$s['id']}' ".($f_sess==$s['id']?'selected':'').">{$s['session_name']}</option>"; ?>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <select name="class_id" id="filter_class" class="form-control select2">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $c) echo "<option value='{$c['id']}' ".($f_class==$c['id']?'selected':'').">{$c['class_name']}</option>"; ?>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <select name="section_id" id="filter_section" class="form-control select2">
                            <option value="">All Sections</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <select name="student_status" class="form-control select2">
                            <option value="All" <?= ($f_stat=='All'?'selected':'') ?>>All Records</option>
                            <option value="Active" <?= ($f_stat=='Active'?'selected':'') ?>>Active Only</option>
                            <option value="Passout" <?= ($f_stat=='Passout'?'selected':'') ?>>Passout Only</option>
                            <option value="Dropout" <?= ($f_stat=='Dropout'?'selected':'') ?>>Dropout Only</option>
                            <option value="Archived" <?= ($f_stat=='Archived'?'selected':'') ?>>Archived Only</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <button type="submit" class="btn btn-primary btn-block shadow-sm"><i class="fa fa-search"></i> Fetch Registry</button>
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
                            <th>Name</th>
                            <th>Class (Sec)</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $count = 1;
                          foreach ($students as $row):
                            $photo = (!empty($row['student_photo']) && file_exists($row['student_photo'])) ? $row['student_photo'] : 'assets/img/userdummypic.png';
                            
                            // STATUS LOGIC
                            $is_passout = ($row['is_passout'] == 1);
                            $is_dropout = ($row['is_dropout'] == 1);
                            $is_archived = ($row['is_deleted'] == 1);
                            $is_active = (!$is_passout && !$is_dropout && !$is_archived);

                            if ($is_passout) {
                                $status = '<span class="badge badge-success badge-status">Passout</span>';
                            } elseif ($is_dropout) {
                                $status = '<span class="badge badge-danger badge-status">Dropout</span>';
                            } elseif ($is_archived) {
                                $status = '<span class="badge badge-secondary badge-status">Archived</span>';
                            } else {
                                $status = '<span class="badge badge-primary badge-status">Active</span>';
                            }
                          ?>
                            <tr>
                              <td><?= $count++ ?></td>
                              <td class="font-weight-bold "><?= $row['reg_no'] ?></td>
                              <td><img src="<?= $photo ?>" class="st-img-circle"></td>
                              <td class="text-uppercase small font-weight-bold"><?= $row['student_name'] ?></td>
                              <td><?= $row['class_name'] ?> (<?= $row['section_name'] ?>)</td>
                              <td><?= $status ?></td>
                              <td>
                                <div class="d-flex align-items-center gap-1">
                                  <!-- VIEW & EDIT: Available for Everyone -->
                                  <a href="student-detail-page.php?id=<?= $row['id'] ?>" class="btn btn-success btn-circle btn-xs" title="View Detail"><i class="fa fa-eye"></i></a>
                                  <a href="student-edit-page.php?id=<?= $row['id'] ?>" class="btn btn-info btn-circle btn-xs" title="Edit Record"><i class="fas fa-pencil-alt"></i></a>
                                  
                                  <!-- DELETE: Only for Active, Locked for Others -->
                                  <?php if($is_active): ?>
                                    <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-danger btn-circle btn-xs" title="Soft Delete"><i class="fa fa-times"></i></a>
                                  <?php else: ?>
                                    <button class="btn btn-light btn-circle btn-xs" disabled title="Archive Locked"><i class="fa fa-lock"></i></button>
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

      var table = $('#tableExportImages').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'print', 'pdf']
      });

      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('status') === 'deleted') {
        Swal.fire('Archived!', 'Student moved to archives successfully.', 'success').then(() => {
          window.history.replaceState({}, document.title, window.location.pathname);
        });
      }

      // Dynamic Section Loader
      $('#student_list_card #filter_class').on('change', function() {
        var cid = $(this).val();
        if(cid) {
          $.getJSON('student-list.php?action=fetch_sections&class_id=' + cid, function(data) {
            var h = '<option value="">All Sections</option>';
            data.forEach(d => {
              let sel = (d.id == "<?= @$_GET['section_id'] ?>") ? 'selected' : '';
              h += `<option value="${d.id}" ${sel}>${d.section_name}</option>`;
            });
            $('#student_list_card #filter_section').html(h);
          });
        }
      });
      if ($('#filter_class').val()) $('#filter_class').trigger('change');
    });

    function confirmDelete(id) {
      Swal.fire({
        title: 'Archive Student?',
        text: "Media will be shifted to deleted_media folder.",
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