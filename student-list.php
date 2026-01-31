<?php

/**
 * 1. DATABASE CONNECTION & SETTINGS
 */
require_once 'auth.php';

// اسکول کی پروفائل سیٹنگز فیچ کریں (صرف PDF ایکسپورٹ میں استعمال کرنے کے لیے)
$school_settings = $pdo->query("SELECT * FROM school_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$sch_name    = !empty($school_settings['school_name']) ? $school_settings['school_name'] : "Amina Girls High School";
$sch_address = !empty($school_settings['address'])     ? $school_settings['address']     : "Adda Sikandri 21/MPR Gailywal, Lodhran";
$sch_contact = !empty($school_settings['contact'])     ? $school_settings['contact']     : "0300-1234567";

// --- AJAX: Fetch Sections ---
if (isset($_GET['action']) && $_GET['action'] == 'fetch_sections') {
  $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
  $stmt->execute([$_GET['class_id'] ?? 0]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

// --- SOFT DELETE / ARCHIVE LOGIC ---
if (isset($_GET['delete_id'])) {
  $id = $_GET['delete_id'];
  $pdo->prepare("UPDATE students SET is_deleted = 1 WHERE id = ?")->execute([$id]);
  header("Location: student-list.php?status=archived");
  exit;
}

/**
 * 2. CONFIG: EXPORT FIELDS ARRAY
 */
$export_fields = [
  'reg_no'           => 'Reg #',
  'student_name'     => 'Student Name',
  'guardian_name'    => 'Father Name',
  'class_name'       => 'Class',
  'section_name'     => 'Section',
  'cnic_bform'       => 'CNIC/B-Form',
  'dob'              => 'Date of Birth',
  'gender'           => 'Gender',
  // 'admission_date'   => 'Adm. Date',
  'student_contact'  => 'Contact',
  'student_address'  => 'Address',
  // 'guardian_contact' => 'Guardian Contact',
  // 'guardian_cnic'    => 'Guardian CNIC'
];

/**
 * 3. GLOBAL FETCH & FILTERS
 */
$classes = $pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC")->fetchAll();

$f_sess = $_GET['session_id'] ?? '';
$f_class = $_GET['class_id'] ?? '';
$f_sec = $_GET['section_id'] ?? '';
$f_stat = $_GET['student_status'] ?? 'All';

$where = ["s.is_deleted = 0"];
$params = [];

if (!empty($f_sess)) {
  $where[] = "s.session = ?";
  $params[] = $f_sess;
}
if (!empty($f_class)) {
  $where[] = "s.class_id = ?";
  $params[] = $f_class;
}
if (!empty($f_sec)) {
  $where[] = "s.section_id = ?";
  $params[] = $f_sec;
}

if ($f_stat == 'Active')  $where[] = "s.is_passout = 0 AND s.is_dropout = 0";
elseif ($f_stat == 'Passout') $where[] = "s.is_passout = 1";
elseif ($f_stat == 'Dropout') $where[] = "s.is_dropout = 1";

$query = "SELECT s.*, c.class_name, sec.section_name 
          FROM students s
          LEFT JOIN classes c ON s.class_id = c.id
          LEFT JOIN sections sec ON s.section_id = sec.id
          WHERE " . implode(" AND ", $where) . " ORDER BY s.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Student List | <?= $sch_name ?></title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <style>
    .st-img-circle {
      width: 35px;
      height: 35px;
      object-fit: cover;
      border-radius: 50%;
      border: 1px solid #ddd;
    }

    .export-only {
      display: none;
    }

    .badge-status {
      font-size: 9px;
      text-transform: uppercase;
      font-weight: 700;
      padding: 4px 8px;
    }

    /* Small Export Buttons Style */
    .dt-buttons .btn {
      padding: 5px 10px !important;
      font-size: 12px !important;
      border-radius: 4px !important;
      margin-right: 5px !important;
      height: 30px !important;
      /* Fixed height to keep it short */
      display: inline-flex;
      align-items: center;
      justify-content: center;
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
                    <h5 class="page-title mb-0">List All Students</h5>
                    <nav aria-label="breadcrumb">
                      <ol class="breadcrumb mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active">List</li>
                      </ol>
                    </nav>
                  </div>
                </div>
              </div>
            </div>

            <!-- Filters Section -->
            <div class="card shadow-sm no-print">
              <div class="card-body border-bottom">
                <form method="GET">
                  <div class="row">
                    <div class="col-md-2">
                      <label class="small font-weight-bold">Session</label>
                      <select name="session_id" class="form-control select2">
                        <option value="">All</option>
                        <?php foreach ($sessions as $s) echo "<option value='{$s['id']}' " . ($f_sess == $s['id'] ? 'selected' : '') . ">{$s['session_name']}</option>"; ?>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label class="small font-weight-bold">Class</label>
                      <select name="class_id" id="filter_class" class="form-control select2">
                        <option value="">All</option>
                        <?php foreach ($classes as $c) echo "<option value='{$c['id']}' " . ($f_class == $c['id'] ? 'selected' : '') . ">{$c['class_name']}</option>"; ?>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label class="small font-weight-bold">Section</label>
                      <select name="section_id" id="filter_section" class="form-control select2">
                        <option value="">All</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="small font-weight-bold">Status Profile</label>
                      <select name="student_status" class="form-control select2">
                        <option value="All" <?= ($f_stat == 'All' ? 'selected' : '') ?>>All Registered</option>
                        <option value="Active" <?= ($f_stat == 'Active' ? 'selected' : '') ?>>Active Only</option>
                        <option value="Passout" <?= ($f_stat == 'Passout' ? 'selected' : '') ?>>Passout Only</option>
                        <option value="Dropout" <?= ($f_stat == 'Dropout' ? 'selected' : '') ?>>Dropout Only</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>&nbsp;</label>
                      <button type="submit" class="btn btn-primary btn-block shadow-sm">Search Records</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Student Table -->
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="professionalExportTable" style="width:100%;">
                    <thead>
                      <tr>
                        <th>S#</th>
                        <th class="no-export">Image</th>
                        <?php
                        foreach ($export_fields as $col => $label) {
                          $is_hidden = !in_array($col, ['reg_no', 'student_name', 'guardian_name', 'class_name', 'section_name', 'cnic_bform']);
                          $cls = $is_hidden ? 'export-only' : '';
                          echo "<th class='$cls'>$label</th>";
                        }
                        ?>
                        <th>Status</th>
                        <th class="no-export">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php $count = 1;
                      foreach ($students as $row): ?>
                        <tr>
                          <td><?= $count++ ?></td>
                          <td class="no-export text-center">
                            <img src="<?= (!empty($row['student_photo']) && file_exists($row['student_photo'])) ? $row['student_photo'] : 'assets/img/userdummypic.png' ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #eee;">
                          </td>

                          <?php
                          foreach ($export_fields as $col => $label) {
                            $is_hidden = !in_array($col, ['reg_no', 'student_name', 'guardian_name', 'class_name', 'section_name', 'cnic_bform']);
                            $cls = $is_hidden ? 'export-only' : '';
                            echo "<td class='$cls'>" . htmlspecialchars($row[$col]) . "</td>";
                          }
                          ?>

                          <td>
                            <?php
                            if ($row['is_passout']) echo '<span class="badge badge-success badge-status">Passout</span>';
                            elseif ($row['is_dropout']) echo '<span class="badge badge-danger badge-status">Dropout</span>';
                            else echo '<span class="badge badge-primary badge-status">Active</span>';
                            ?>
                          </td>
                          <td class="no-export">
                            <div class="d-flex">
                              <a href="student-detail-page.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm mr-1" title="View"><i class="fa fa-eye"></i></a>
                              <a href="student-edit-page.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm mr-1" title="Edit"><i class="fa fa-edit"></i></a>
                              <button onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-danger btn-sm" title="Archive"><i class="fa fa-trash"></i></button>
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
        </section>
      </div>

      <footer class="main-footer no-print">
        <div class="footer-left">
          Student Management System | Designed & Developed by <strong>Tanveer Ahmad</strong>
        </div>
      </footer>
    </div>
  </div>

  <!-- Essential Scripts -->
  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>

  <!-- Export Dependencies - Ensuring PDF compatibility -->
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>

  <script src="assets/js/scripts.js"></script>
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>

  <script>
    $(document).ready(function() {
      var schName = "<?= $sch_name ?>";
      var schAddr = "<?= $sch_address ?>";
      var schContact = "<?= $sch_contact ?>";
      var dateTime = "<?= date('d-M-Y h:i A') ?>";

      $('#professionalExportTable').DataTable({
        dom: 'Bfrtip',
        buttons: [{
            extend: 'excel',
            text: '<i class="fa fa-file-excel"></i>', // Icon Only
            titleAttr: 'Export to Excel',
            className: 'btn btn-success btn-sm',
            title: schName + '_Students_Record',
            exportOptions: {
              columns: ':not(.no-export)'
            }
          },
          {
            extend: 'pdf',
            text: '<i class="fa fa-file-pdf"></i>', // Icon Only
            titleAttr: 'Export Official PDF',
            className: 'btn btn-danger btn-sm',
            orientation: 'landscape',
            pageSize: 'A4',
            exportOptions: {
              columns: ':not(.no-export)'
            },
            customize: function(doc) {
              // 1. Watermark
              doc.watermark = {
                text: schName.toUpperCase(),
                color: '#ddd',
                opacity: 0.1,
                bold: true
              };

              // 2. Official Header
              doc.content.splice(0, 0, {
                margin: [0, 0, 0, 10],
                alignment: 'center',
                text: schName.toUpperCase(),
                fontSize: 22,
                bold: true
              }, {
                margin: [0, 0, 0, 5],
                alignment: 'center',
                text: schAddr,
                fontSize: 10
              });

              // 3. Signature Line
              doc.content.push({
                margin: [0, 50, 0, 0],
                columns: [{
                    text: '',
                    width: '*'
                  },
                  {
                    width: 200,
                    text: '__________________________\nAuthorized Signature / Stamp',
                    alignment: 'center',
                    bold: true,
                    fontSize: 10
                  }
                ]
              });

              // 4. Footer
              doc['footer'] = (function(page, pages) {
                return {
                  columns: [{
                      alignment: 'left',
                      text: 'Printed Desk (AGHS): ' + dateTime,
                      margin: [30, 0]
                    },
                    {
                      alignment: 'right',
                      text: 'AGHS Student Management System',
                      margin: [0, 0, 30, 0]
                    }
                  ],
                  fontSize: 8,
                  color: '#666'
                }
              });

              doc.styles.tableHeader.fillColor = '#f2f2f2';
              doc.styles.tableHeader.color = 'black';
              doc.styles.tableHeader.alignment = 'center';
            }
          },
          {
            extend: 'print',
            text: '<i class="fa fa-print"></i>', // Icon Only
            titleAttr: 'Print Student List',
            className: 'btn btn-primary btn-sm',
            exportOptions: {
              columns: ':not(.no-export)'
            }
          }
        ]
      });

      // Dynamic Section Loader
      $('#filter_class').on('change', function() {
        var cid = $(this).val();
        if (cid) {
          $.getJSON('student-list.php?action=fetch_sections&class_id=' + cid, function(data) {
            var h = '<option value="">All</option>';
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
      swal({
        title: "Archive Record?",
        text: "This student will be moved to archived list.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
      }).then((willDelete) => {
        if (willDelete) {
          window.location.href = "student-list.php?delete_id=" + id;
        }
      });
    }
  </script>
</body>

</html>