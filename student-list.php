<?php
/**
 * 1. DATABASE CONNECTION & AUTHENTICATION
 */
require_once 'auth.php';

// --- AJAX Handler for Sections (Filter Dropdown) ---
if (isset($_GET['action']) && $_GET['action'] == 'fetch_sections') {
  $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
  $stmt->execute([$_GET['class_id'] ?? 0]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

/**
 * 2. SOFT DELETE & FILE MOVING LOGIC
 * Files are cut from 'uploads/' and pasted into 'deleted_media/'
 */
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    // Get current file paths
    $stmt = $pdo->prepare("SELECT student_photo, cnic_doc, guardian_cnic_front, guardian_cnic_back, result_card_doc FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $trashFolder = 'deleted_media/';
        if (!is_dir($trashFolder)) mkdir($trashFolder, 0777, true);

        // Move each file to trash folder
        foreach ($student as $key => $filePath) {
            if (!empty($filePath) && file_exists($filePath)) {
                $fileName = basename($filePath);
                $newPath = $trashFolder . $fileName;
                
                if (rename($filePath, $newPath)) {
                    // Update database with new path
                    $upd = $pdo->prepare("UPDATE students SET $key = ? WHERE id = ?");
                    $upd->execute([$newPath, $id]);
                }
            }
        }

        // Set Soft Delete flag
        $stmt = $pdo->prepare("UPDATE students SET is_deleted = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: student-list.php?status=deleted");
    exit;
}

/**
 * 3. FETCH DATA (Excluding Soft Deleted Records)
 */
$classes = $pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);

$where_clauses = ["s.is_deleted = 0"]; 
$params = [];

if (!empty($_GET['class_id'])) {
  $where_clauses[] = "s.class_id = ?";
  $params[] = $_GET['class_id'];
}
if (!empty($_GET['section_id'])) {
  $where_clauses[] = "s.section_id = ?";
  $params[] = $_GET['section_id'];
}

$query = "SELECT s.id, s.reg_no, s.student_name, s.student_photo, c.class_name, sec.section_name 
          FROM students s
          LEFT JOIN classes c ON s.class_id = c.id
          LEFT JOIN sections sec ON s.section_id = sec.id";

if (count($where_clauses) > 0) {
  $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY s.id DESC";
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
  <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />
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
                      <h5 class="page-title mb-0">List Students</h5>
                      <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 bg-transparent p-0">
                          <li class="breadcrumb-item"><a href="#"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                          <li class="breadcrumb-item active">List Students</li>
                        </ol>
                      </nav>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fa fa-users"></i>&nbsp;Filter Records</p>
                  </div>

                  <div class="card-body border-bottom">
                    <form method="GET">
                      <div class="row">
                        <div class="col-md-4">
                          <select name="class_id" id="filter_class" class="form-control select2">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $c): ?>
                              <option value="<?= $c['id'] ?>" <?= (isset($_GET['class_id']) && $_GET['class_id'] == $c['id']) ? 'selected' : '' ?>><?= $c['class_name'] ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <select name="section_id" id="filter_section" class="form-control select2">
                            <option value="">Select Section</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Fetch Students</button>
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
                            <th>Class</th>
                            <th>Section</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $count = 1; foreach ($students as $row): 
                            $photo = (!empty($row['student_photo']) && file_exists($row['student_photo'])) ? $row['student_photo'] : 'assets/img/userdummypic.png';
                          ?>
                            <tr>
                              <td><?= $count++ ?></td>
                              <td class="font-weight-bold "><?= $row['reg_no'] ?></td>
                              <td><img src="<?= $photo ?>" class="rounded-circle" width="35" height="35" style="object-fit: cover; border: 1px solid #ddd;"></td>
                              <td class="text-uppercase"><?= $row['student_name'] ?></td>
                              <!-- SEPARATE COLUMNS -->
                              <td><?= $row['class_name'] ?></td>
                              <td><?= $row['section_name'] ?></td>
                              <td>
                                <div class="d-flex align-items-center gap-1">
                                  <a href="student-detail-page.php?id=<?= $row['id'] ?>" class="btn btn-success btn-circle btn-xs" title="View"><i class="fa fa-eye"></i></a>
                                  <a href="student-edit-page.php?id=<?= $row['id'] ?>" class="btn btn-info btn-circle btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                  <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-danger btn-circle btn-xs" title="Soft Delete"><i class="fa fa-times"></i></a>
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

  <!-- Scripts -->
  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.flash.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    $(document).ready(function() {
      $('.loader').fadeOut('slow');

      // --- DataTables with Image PDF Export Logic ---
      var table = $('#tableExportImages').DataTable({
        dom: 'Bfrtip',
        buttons: [
          'copy', 'csv', 'excel', 'print',
          {
            extend: 'pdfHtml5',
            orientation: 'portrait',
            pageSize: 'A4',
            exportOptions: { columns: [0, 1, 2, 3, 4, 5], stripHtml: false },
            customize: function(doc) {
              // Convert table images to Base64 for the PDF
              for (var i = 1; i < doc.content[1].table.body.length; i++) {
                var canvas = document.createElement('canvas');
                var img = table.cell(i - 1, 2).node().querySelector('img');
                canvas.width = img.width; 
                canvas.height = img.height;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, img.width, img.height);
                var dataURL = canvas.toDataURL('image/png');
                // Replace text in Column 2 with the actual Image
                doc.content[1].table.body[i][2] = { image: dataURL, width: 25 };
              }
              // Adjust column widths for the 6 columns
              doc.content[1].table.widths = ['5%', '15%', '10%', '35%', '20%', '15%'];
            }
          }
        ]
      });

      // --- URL Cleanup (Refresh logic fix) ---
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('status') === 'deleted') {
        Swal.fire('Archived!', 'Student record moved to archives.', 'success').then(() => {
          window.history.replaceState({}, document.title, window.location.pathname);
        });
      }

      // --- Dynamic Sections ---
      $('#filter_class').on('change', function() {
        var cid = $(this).val();
        if(cid) {
          $.getJSON('student-list.php?action=fetch_sections&class_id=' + cid, function(data) {
            var h = '<option value="">Select Section</option>';
            data.forEach(d => {
              let sel = (d.id == "<?= $_GET['section_id'] ?? '' ?>") ? 'selected' : '';
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
        title: 'Move to Archives?',
        text: "Student will be soft-deleted and files moved to trash folder.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it!'
      }).then((result) => {
        if (result.isConfirmed) window.location.href = 'student-list.php?delete_id=' + id;
      });
    }
  </script>
</body>
</html>