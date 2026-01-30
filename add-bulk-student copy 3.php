<?php
ob_start();
require_once 'auth.php';

// --- 1. AJAX ACTIONS ---
if (isset($_GET['action'])) {
  ob_clean();
  header('Content-Type: application/json');
  if ($_GET['action'] == 'get_all_sessions') {
    $stmt = $pdo->query("SELECT id, session_name, is_active FROM academic_sessions");
    echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
  } elseif ($_GET['action'] == 'fetch_sections') {
    $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
    $stmt->execute([$_GET['class_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  } elseif ($_GET['action'] == 'fetch_routes') {
    $stmt = $pdo->query("SELECT id, route_name FROM transport_routes");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  exit;
}

$success = false;
$error = "";
$count = 0;

// --- 2. IMPORT LOGIC (Strictly CSV Columns) ---
if (isset($_POST['import_bulk'])) {
  try {
    if ($_FILES['student_file']['size'] > 0) {
      $handle = fopen($_FILES['student_file']['tmp_name'], "r");
      fgetcsv($handle); // Header Skip

      $pdo->beginTransaction();
      $sql = "INSERT INTO students (
                id, reg_no, admission_date, session, class_id, section_id, subject_group_id, 
                student_name, cnic_bform, dob, gender, mother_language, caste, contact_no, 
                address, guardian_name, relation, occupation, guardian_contact, 
                prev_school_name, last_class, passing_year, board_name, disability, 
                hafiz_quran, transport, route_id, interests, remarks, student_photo, 
                cnic_doc, guardian_cnic_front, guardian_cnic_back, result_card_doc, 
                created_at, is_deleted, is_promoted, is_detained, is_dropout, is_passout, is_certified
            ) VALUES (NULL, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW(), 0,0,0,0,0,0)";

      $stmt = $pdo->prepare($sql);
      while (($col = fgetcsv($handle, 10000, ",")) !== FALSE) {
        if (empty($col[6])) continue;
        $dob = (!empty($col[8])) ? date('Y-m-d', strtotime($col[8])) : NULL;
        $adm_date = (!empty($col[1])) ? date('Y-m-d', strtotime($col[1])) : date('Y-m-d');

        $stmt->execute([
          $col[0],
          $adm_date,
          $col[2],
          $col[3],
          $col[4],
          $col[5],
          strtoupper($col[6]),
          $col[7],
          $dob,
          $col[9],
          $col[10],
          $col[11],
          $col[12],
          $col[13],
          $col[14],
          $col[15],
          $col[16],
          $col[17],
          $col[18],
          $col[19],
          $col[20],
          $col[21],
          $col[22],
          $col[23],
          $col[24],
          $col[25],
          trim($col[26]),
          $col[27],
          $col[28],
          $col[29],
          $col[30],
          $col[31],
          $col[32]
        ]);
        $count++;
      }
      $pdo->commit();
      $success = true;
    }
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $error = $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Bulk Admission | AGS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <style>
    .route-ref-card {
      max-height: 450px;
      overflow-y: auto;
      border-radius: 8px;
    }

    .table-sticky thead th {
      position: sticky;
      top: 0;
      background: #6777ef;
      color: white;
      z-index: 1;
    }

    .navbar-bg {
      height: 70px !important;
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

            <div class="row bg-title mb-4">
              <div class="col-12">
                <div class="card mb-0 shadow-sm">
                  <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">Bulk Student Admission</h5>
                    <nav aria-label="breadcrumb">
                      <ol class="breadcrumb mb-0 p-0 text-small">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Bulk Import</li>
                      </ol>
                    </nav>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-9">
                <div class="card  py-2 shadow-sm">
                  <div class="card-header d-flex justify-content-between">
                    <h4><i class="fas fa-file-csv"></i> Admission Process</h4>
                    <button type="button" id="get_template" class="btn btn-outline-success btn-sm font-weight-bold">
                      <i class="fas fa-download"></i> Download Template
                    </button>
                  </div>
                  <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">

                      <div class="row border-bottom mb-4 pb-2">
                        <div class="col-md-3 mb-3">
                          <label class="font-weight-bold">Session ID</label>
                          <select id="s_sess" class="form-control select2"></select>
                        </div>
                        <div class="col-md-3 mb-3">
                          <label class="font-weight-bold">Class ID</label>
                          <select id="s_cls" class="form-control select2">
                            <option value="">-- Select --</option>
                            <?php foreach ($pdo->query("SELECT * FROM classes")->fetchAll() as $c) : ?>
                              <option value="<?= $c['id'] ?>"><?= $c['class_name'] ?> (ID: <?= $c['id'] ?>)</option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-3 mb-3">
                          <label class="font-weight-bold">Section ID</label>
                          <select id="s_sec" class="form-control select2">
                            <option value="">-- Select --</option>
                          </select>
                        </div>
                        <div class="col-md-3 mb-3">
                          <label class="font-weight-bold">Group ID</label>
                          <select id="s_grp" class="form-control select2">
                            <option value="">-- Select --</option>
                            <?php foreach ($pdo->query("SELECT * FROM subject_groups")->fetchAll() as $g) : ?>
                              <option value="<?= $g['id'] ?>"><?= $g['group_name'] ?> (ID: <?= $g['id'] ?>)</option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="font-weight-bold">Choose CSV File</label>
                        <div class="custom-file">
                          <input type="file" name="student_file" class="custom-file-input" id="customFile" accept=".csv" required>
                          <label class="custom-file-label">Select file</label>
                        </div>
                        <small class="text-muted mt-2 d-block">System will use IDs from CSV for final registration.</small>
                      </div>

                      <div class="card-footer bg-whitesmoke text-right rounded">
                        <button type="submit" name="import_bulk" class="btn btn-success btn-lg shadow">
                          <i class="fas fa-check-circle"></i> Import Now
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <div class="col-lg-3">
                <div class="card py-2 shadow-sm">
                  <div class="card-header py-2 text-center">
                    <h6 class="mb-0">Transport Route IDs</h6>
                  </div>
                  <div class="card-body p-0 route-ref-card">
                    <table class="table table-sm table-striped table-sticky text-center mb-0">
                      <tbody id="r_data">
                      </tbody>
                    </table>
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
  <script src="./assets/js/sweetalert2.js"></script>
  <script src="assets/js/scripts.js"></script>

  <script>
    $(document).ready(function() {
      // Load Sessions & Routes
      $.getJSON('?action=get_all_sessions', function(res) {
        let h = '';
        res.data.forEach(s => h += `<option value="${s.id}" ${(s.is_active==1?'selected':'')}>${s.session_name} (ID: ${s.id})</option>`);
        $('#s_sess').html(h);
      });

      $.getJSON('?action=fetch_routes', function(data) {
        let h = '';
        data.forEach(r => h += `<tr><td class="font-weight-bold ">${r.id}</td><td class="small text-left">${r.route_name}</td></tr>`);
        $('#r_data').html(h);
      });

      // Chain Class -> Section
      $('#s_cls').on('change', function() {
        $.getJSON('?action=fetch_sections&class_id=' + $(this).val(), function(data) {
          let h = '<option value="">-- Select Section --</option>';
          data.forEach(d => h += `<option value="${d.id}">${d.section_name} (ID: ${d.id})</option>`);
          $('#s_sec').html(h);
        });
      });

      // Download Template
      $('#get_template').click(function() {
        let cls = $('#s_cls').val(),
          sec = $('#s_sec').val();
        if (!cls || !sec) {
          Swal.fire('Error', 'Pehle Class aur Section select karein!', 'error');
          return;
        }
        window.location.href = `generate_csv.php?session_id=${$('#s_sess').val()}&class_id=${cls}&section_id=${sec}&subject_group_id=${$('#s_grp').val()}`;
      });

      $('#customFile').on('change', function() {
        $(this).next('.custom-file-label').html(this.files[0].name);
      });

      <?php if ($success): ?> Swal.fire('Done!', '<?= $count ?> Students Added.', 'success');
      <?php endif; ?>
      <?php if ($error): ?> Swal.fire('Error!', '<?= addslashes($error) ?>', 'error');
      <?php endif; ?>
    });
  </script>
</body>

</html>