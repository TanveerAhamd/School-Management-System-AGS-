<?php

/**
 * 1. DATABASE CONNECTION & AJAX HANDLERS
 */
require_once 'auth.php';

if (isset($_GET['action'])) {
  header('Content-Type: application/json');

  // Action: Fetch Sections
  if ($_GET['action'] == 'fetch_sections') {
    $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
    $stmt->execute([$_GET['class_id'] ?? 0]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
  }

  // Action: Global Fetch Students (Filters are Optional)
  if ($_GET['action'] == 'fetch_students_to_process') {
    $sess = $_GET['curr_session'] ?? '';
    $cls  = $_GET['curr_class'] ?? '';
    $sec  = $_GET['curr_section'] ?? '';
    $stat = $_GET['curr_status'] ?? 'All';

    $query = "SELECT s.id, s.reg_no, s.student_name, s.student_photo, s.is_deleted, s.is_passout, s.is_dropout,
                         c.class_name, sec.section_name 
                  FROM students s
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN sections sec ON s.section_id = sec.id
                  WHERE 1=1";

    $params = [];
    if (!empty($sess)) {
      $query .= " AND s.session = ?";
      $params[] = $sess;
    }
    if (!empty($cls)) {
      $query .= " AND s.class_id = ?";
      $params[] = $cls;
    }
    if (!empty($sec)) {
      $query .= " AND s.section_id = ?";
      $params[] = $sec;
    }

    if ($stat == 'Active') {
      $query .= " AND s.is_deleted = 0";
    } elseif ($stat == 'Dropout') {
      $query .= " AND s.is_dropout = 1";
    } elseif ($stat == 'Passout') {
      $query .= " AND s.is_passout = 1";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
  }

  // Action: Process Dropout/Passout
  if ($_GET['action'] == 'process_students' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
      $ids = $_POST['student_ids'];
      $type = $_POST['process_type'];

      if (empty($ids)) throw new Exception("Please select students.");

      $trashFolder = 'deleted_media/';
      if (!is_dir($trashFolder)) mkdir($trashFolder, 0777, true);

      foreach ($ids as $stId) {
        $stmt = $pdo->prepare("SELECT student_photo, cnic_doc, guardian_cnic_front, guardian_cnic_back, result_card_doc FROM students WHERE id = ?");
        $stmt->execute([$stId]);
        $files = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($files) {
          foreach ($files as $col => $path) {
            if (!empty($path) && file_exists($path)) {
              $newPath = $trashFolder . basename($path);
              if (rename($path, $newPath)) {
                $pdo->prepare("UPDATE students SET $col = ? WHERE id = ?")->execute([$newPath, $stId]);
              }
            }
          }
        }

        $is_pass = ($type === 'Passout') ? 1 : 0;
        $is_drop = ($type === 'Dropout') ? 1 : 0;
        $remark = strtoupper("Status: $type on " . date('d-M-Y H:i'));

        $sql = "UPDATE students SET is_deleted = 1, is_passout = ?, is_dropout = ?, remarks = CONCAT(IFNULL(remarks,''), '\n', ?) WHERE id = ?";
        $pdo->prepare($sql)->execute([$is_pass, $is_drop, $remark, $stId]);
      }
      echo json_encode(['status' => 'success', 'message' => "Selected students marked as $type."]);
    } catch (Exception $e) {
      echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
  }
}

$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Student Archives | AGS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />
  <style>
    #dropout_container .st-list-img {
      height: 30px;
      width: 30px;
      object-fit: cover;
      border-radius: 50%;
      border: 1px solid #ddd;
    }

    .badge-status {
      font-size: 10px;
      padding: 4px 8px;
      text-transform: uppercase;
    }

    .dt-buttons {
      margin-bottom: 15px;
    }
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
                      <h5 class="page-title mb-0"><i class="fas fa-archive"></i> Student Dropout & Passout Archives</h5>
                      <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 bg-transparent p-0">
                          <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                          <li class="breadcrumb-item active">Dropout_passout</li>
                        </ol>
                      </nav>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="card" id="dropout_container">

                  <!-- FILTER PANEL -->
                  <div class="card-body border-bottom bg-light-all">
                    <div class="row">
                      <div class="col-md-2">
                        <label class="font-weight-bold small">Session</label>
                        <select id="f_session" class="form-control select2">
                          <option value="">All Sessions</option>
                          <?php foreach ($sessions as $s) echo "<option value='{$s['id']}'>{$s['session_name']}</option>"; ?>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label class="font-weight-bold small">Class</label>
                        <select id="f_class" class="form-control select2">
                          <option value="">All Classes</option>
                          <?php foreach ($classes as $c) echo "<option value='{$c['id']}'>{$c['class_name']}</option>"; ?>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label class="font-weight-bold small">Section</label>
                        <select id="f_section" class="form-control select2">
                          <option value="">All Sections</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label class="font-weight-bold small">Current Status</label>
                        <select id="f_status" class="form-control select2">
                          <option value="All">All Records</option>
                          <option value="Active" selected>Active Only</option>
                          <option value="Dropout">Dropout Only</option>
                          <option value="Passout">Passout Only</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label class="d-block">&nbsp;</label>
                        <button type="button" id="btnSearch" class="btn btn-primary btn-block shadow-sm"><i class="fa fa-search"></i> Fetch Data</button>
                      </div>
                    </div>
                  </div>

                  <div class="card-body">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                      <h6 class="font-weight-bold text-muted small text-uppercase">Student Records:</h6>
                      <div id="action_btns_scoped" style="display:none;">
                        <button type="button" class="btn btn-danger mr-2 btnAction" data-type="Dropout"><i class="fas fa-user-times"></i> Mark Dropout</button>
                        <button type="button" class="btn btn-success btnAction" data-type="Passout"><i class="fas fa-user-graduate"></i> Mark Passout</button>
                      </div>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="archiveTable" style="width:100%;">
                        <thead>
                          <tr>
                            <th width="5%"><input type="checkbox" id="masterCheck"></th>
                            <th>Reg#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody id="student_list_container">
                          <tr>
                            <td colspan="7" class="text-center">Set filters to load students.</td>
                          </tr>
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

  <canvas id="hidden_canvas" style="display:none;"></canvas>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.flash.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>

  <script src="./assets/js/sweetalert2.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    $(document).ready(function() {
      $('.loader').fadeOut('slow');

      // 1. Scoped Section Loader
      $('#dropout_container #f_class').on('change', function() {
        let cid = $(this).val();
        if (cid) {
          $.getJSON('passout_drop.php?action=fetch_sections&class_id=' + cid, function(data) {
            let h = '<option value="">All Sections</option>';
            data.forEach(d => h += `<option value="${d.id}">${d.section_name}</option>`);
            $('#dropout_container #f_section').html(h);
          });
        }
      });

      // 2. Master Checkbox
      $('#dropout_container #masterCheck').on('click', function() {
        $('#dropout_container .st-cb').prop('checked', this.checked);
      });

      // 3. AJAX Fetch Students + DataTable Export Initialization
      $('#dropout_container #btnSearch').click(function() {
        let sess = $('#f_session').val(),
          cls = $('#f_class').val(),
          sec = $('#f_section').val(),
          stat = $('#f_status').val();

        $.getJSON('passout_drop.php?action=fetch_students_to_process', {
          curr_session: sess,
          curr_class: cls,
          curr_section: sec,
          curr_status: stat
        }, function(data) {

          if ($.fn.DataTable.isDataTable('#archiveTable')) {
            $('#archiveTable').DataTable().destroy();
          }

          let h = '';
          if (data.length > 0) {
            data.forEach(s => {
              let photo = s.student_photo ? s.student_photo : 'assets/img/userdummypic.png';
              let disp = s.is_passout == 1 ? 'Passout' : (s.is_dropout == 1 ? 'Dropout' : 'Active');
              let bCls = s.is_passout == 1 ? 'badge-success' : (s.is_dropout == 1 ? 'badge-danger' : 'badge-primary');

              h += `<tr>
                                <td>${(disp == 'Active') ? `<input type="checkbox" class="st-cb" value="${s.id}">` : `<i class="fa fa-lock text-muted"></i>`}</td>
                                <td class="font-weight-bold">${s.reg_no}</td>
                                <td><img src="${photo}" class="st-list-img"></td>
                                <td class="text-uppercase small font-weight-bold">${s.student_name}</td>
                                <td>${s.class_name || 'N/A'}</td>
                                <td>${s.section_name || 'N/A'}</td>
                                <td><span class="badge ${bCls} badge-status">${disp}</span></td>
                            </tr>`;
            });
          }

          $('#dropout_container #student_list_container').html(h || '<tr><td colspan="7" class="text-center">No records found.</td></tr>');
          $('#dropout_container #masterCheck').prop('checked', false);

          // INITIALIZE DATATABLE WITH EXPORT BUTTONS
          $('#archiveTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
              'copy', 'csv', 'excel', 'print',
              {
                extend: 'pdfHtml5',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5, 6],
                  stripHtml: false
                },
                customize: function(doc) {
                  for (var i = 1; i < doc.content[1].table.body.length; i++) {
                    var canvas = document.createElement('canvas');
                    var img = $('#archiveTable').DataTable().cell(i - 1, 2).node().querySelector('img');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, img.width, img.height);
                    var dataURL = canvas.toDataURL('image/png');
                    doc.content[1].table.body[i][1] = {
                      image: dataURL,
                      width: 20
                    };
                  }
                }
              }
            ]
          });

          if (stat === 'Active' || stat === 'All') $('#action_btns_scoped').fadeIn();
          else $('#action_btns_scoped').fadeOut();
        });
      });

      // 4. Bulk Processing
      $('#dropout_container .btnAction').click(function() {
        let type = $(this).data('type'),
          selected = [];
        $('#dropout_container .st-cb:checked').each(function() {
          selected.push($(this).val());
        });

        if (selected.length === 0) {
          Swal.fire('No Selection', 'Please select students to mark as ' + type, 'warning');
          return;
        }

        Swal.fire({
          title: `Mark as ${type}?`,
          text: `Warning: This moves ${selected.length} students to archives and shifts their media files.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, Process'
        }).then((res) => {
          if (res.isConfirmed) {
            $.post('passout_drop.php?action=process_students', {
              student_ids: selected,
              process_type: type
            }, function(response) {
              if (response.status === 'success') {
                Swal.fire('Success!', response.message, 'success').then(() => {
                  $('#dropout_container #btnSearch').click();
                });
              } else {
                Swal.fire('Error', response.message, 'error');
              }
            });
          }
        });
      });
    });
  </script>
</body>

</html>