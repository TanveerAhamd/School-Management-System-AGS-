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

  // Action: Global Fetch Students with Priority Status Logic
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

    // Status Filter Logic
    if ($stat == 'Active') {
      $query .= " AND s.is_deleted = 0 AND s.is_passout = 0 AND s.is_dropout = 0";
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

  // Action: Process Dropout/Passout (Media Migration + Status Update)
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
              // Rename with ID to prevent overwrite
              $newPath = $trashFolder . $stId . "_" . basename($path);
              if (rename($path, $newPath)) {
                $pdo->prepare("UPDATE students SET $col = ? WHERE id = ?")->execute([$newPath, $stId]);
              }
            }
          }
        }

        $is_pass = ($type === 'Passout') ? 1 : 0;
        $is_drop = ($type === 'Dropout') ? 1 : 0;
        $remark = strtoupper("\n[System Log: Marked as $type on " . date('d-M-Y H:i') . "]");

        // Mark as deleted but keep the specific Passout/Dropout flag
        $sql = "UPDATE students SET is_deleted = 1, is_passout = ?, is_dropout = ?, remarks = CONCAT(IFNULL(remarks,''), ?) WHERE id = ?";
        $pdo->prepare($sql)->execute([$is_pass, $is_drop, $remark, $stId]);
      }
      echo json_encode(['status' => 'success', 'message' => "Selected students successfully marked as $type."]);
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
  <title>Passout_Drop | AGHS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="icon" href="assets/img/favicon.png">
  <style>
    #dropout_container .st-list-img {
      height: 35px;
      width: 35px;
      object-fit: cover;
      border-radius: 50%;
      border: 1px solid #ddd;
    }

    .badge-status {
      font-size: 10px;
      padding: 5px 10px;
      text-transform: uppercase;
      font-weight: 700;
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
                <div class="card mb-3 shadow-sm">
                  <div class="card-body py-2 b-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                      <h5 class="page-title mb-0"><i class="fas fa-user-shield"></i>Dropout & Passout</h5>
                      <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 bg-transparent p-0">
                          <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                          <li class="breadcrumb-item active">Archive_Process</li>
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

                  <!-- ADVANCE FILTER PANEL -->
                  <div class="card-body border-bottom bg-light">
                    <div class="row align-items-end">
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
                        <label class="font-weight-bold small">Filter by Status</label>
                        <select id="f_status" class="form-control select2">
                          <option value="All">Complete History</option>
                          <option value="Active" selected>Active Only (To Process)</option>
                          <option value="Dropout">Dropout Archive</option>
                          <option value="Passout">Passout Archive</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <button type="button" id="btnSearch" class="btn btn-primary btn-block shadow-sm"><i class="fa fa-sync"></i> Fetch Data</button>
                      </div>
                    </div>
                  </div>

                  <div class="card-body">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                      <h6 class="font-weight-bold text-muted small text-uppercase">Records List:</h6>
                      <!-- Bulk Actions -->
                      <div id="action_btns_scoped" style="display:none;">
                        <button type="button" class="btn btn-danger mr-2 btnAction shadow-sm" data-type="Dropout"><i class="fas fa-user-times"></i> Mark Dropout</button>
                        <button type="button" class="btn btn-success btnAction shadow-sm" data-type="Passout"><i class="fas fa-user-graduate"></i> Mark Passout</button>
                      </div>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="archiveTable" style="width:100%;">
                        <thead>
                          <tr>
                            <th width="5%"><input type="checkbox" id="masterCheck"></th>
                            <th>Reg#</th>
                            <th>Image</th>
                            <th>Student Name</th>
                            <th>Class (Section)</th>
                            <th>Current Status</th>
                          </tr>
                        </thead>
                        <tbody id="student_list_container">
                          <tr>
                            <td colspan="6" class="text-center">Please use filters above to load students.</td>
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

      // 1. Dynamic Section Loader
      $('#f_class').on('change', function() {
        let cid = $(this).val();
        if (cid) {
          $.getJSON('passout_drop.php?action=fetch_sections&class_id=' + cid, function(data) {
            let h = '<option value="">All Sections</option>';
            data.forEach(d => h += `<option value="${d.id}">${d.section_name}</option>`);
            $('#f_section').html(h);
          });
        }
      });

      // 2. Master Checkbox Handler
      $('#masterCheck').on('click', function() {
        $('.st-cb').prop('checked', this.checked);
      });

      // 3. AJAX Fetch Students with Priority UI Logic
      $('#btnSearch').click(function() {
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

              // --- PRIORITY LOGIC FOR STATUS DISPLAY ---
              let disp = 'Active';
              let bCls = 'badge-primary';

              if (s.is_passout == 1) {
                disp = 'Passout';
                bCls = 'badge-success';
              } else if (s.is_dropout == 1) {
                disp = 'Dropout';
                bCls = 'badge-danger';
              } else if (s.is_deleted == 1) {
                disp = 'Archived';
                bCls = 'badge-secondary';
              }

              h += `<tr>
                        <td class="text-center">
                            ${(disp == 'Active') ? `<input type="checkbox" class="st-cb" value="${s.id}">` : `<i class="fa fa-lock text-muted" title="Processed Record"></i>`}
                        </td>
                        <td class="font-weight-bold">${s.reg_no}</td>
                        <td><img src="${photo}" class="st-list-img shadow-sm"></td>
                        <td class="text-uppercase small font-weight-bold">${s.student_name}</td>
                        <td>${s.class_name || 'N/A'} (${s.section_name || 'N/A'})</td>
                        <td><span class="badge ${bCls} badge-status">${disp}</span></td>
                    </tr>`;
            });
          }

          $('#student_list_container').html(h || '<tr><td colspan="6" class="text-center">No matching records found.</td></tr>');
          $('#masterCheck').prop('checked', false);

          // 4. DATATABLE WITH EXPORT & PDF THUMBNAILS
          $('#archiveTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
              'copy', 'csv', 'excel', 'print',
              {
                extend: 'pdfHtml5',
                orientation: 'portrait',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5]
                },
                customize: function(doc) {
                  for (var i = 1; i < doc.content[1].table.body.length; i++) {
                    var imgElement = $('#archiveTable').DataTable().row(i - 1).node().querySelector('img');
                    if (imgElement) {
                      var canvas = document.createElement('canvas');
                      canvas.width = imgElement.naturalWidth;
                      canvas.height = imgElement.naturalHeight;
                      var ctx = canvas.getContext('2d');
                      ctx.drawImage(imgElement, 0, 0);
                      var dataURL = canvas.toDataURL('image/png');
                      doc.content[1].table.body[i][1] = {
                        image: dataURL,
                        width: 20
                      };
                    }
                  }
                }
              }
            ]
          });

          // Show/Hide bulk action buttons
          if (stat === 'Active' || stat === 'All') $('#action_btns_scoped').fadeIn();
          else $('#action_btns_scoped').fadeOut();
        });
      });

      // 5. Bulk Processing Logic
      $('.btnAction').click(function() {
        let type = $(this).data('type'),
          selected = [];
        $('.st-cb:checked').each(function() {
          selected.push($(this).val());
        });

        if (selected.length === 0) {
          Swal.fire('Selection Required', 'Please check at least one student to mark as ' + type, 'warning');
          return;
        }

        Swal.fire({
          title: `Confirm ${type} Status?`,
          text: `You are about to mark ${selected.length} students as ${type}. This action will archive their active record and migrate media files.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: (type === 'Passout' ? '#28a745' : '#dc3545'),
          confirmButtonText: 'Yes, Process Now'
        }).then((res) => {
          if (res.isConfirmed) {
            $.post('passout_drop.php?action=process_students', {
              student_ids: selected,
              process_type: type
            }, function(response) {
              if (response.status === 'success') {
                Swal.fire('Success!', response.message, 'success').then(() => {
                  $('#btnSearch').click(); // Refresh list
                });
              } else {
                Swal.fire('System Error', response.message, 'error');
              }
            });
          }
        });
      });
    });
  </script>
</body>

</html>