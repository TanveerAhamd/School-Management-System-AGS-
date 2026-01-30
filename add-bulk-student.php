<?php
ob_start();
require_once 'auth.php';

// --- 1. AJAX ACTIONS ---
if (isset($_GET['action'])) {
  ob_clean();
  header('Content-Type: application/json');
  if ($_GET['action'] == 'get_all_sessions') {
    $stmt = $pdo->query("SELECT id, session_name, is_active FROM academic_sessions ORDER BY id DESC");
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

// --- 2. IMPORT LOGIC (SMART PARSER) ---
if (isset($_POST['import_bulk'])) {
  try {
    if ($_FILES['student_file']['size'] > 0) {
      $handle = fopen($_FILES['student_file']['tmp_name'], "r");

      $pdo->beginTransaction();

      // SQL with ALL 33 Fields
      $sql = "INSERT INTO students (
                reg_no, admission_date, session, class_id, section_id, 
                medium, subject_group_id, student_name, cnic_bform, dob, 
                gender, mother_language, caste, tehsil, district, 
                student_contact, student_address, guardian_name, relation, occupation, 
                guardian_cnic, guardian_contact, guardian_address, prev_school_name, last_class, 
                passing_year, board_name, disability, hafiz_quran, transport, 
                route_id, interests, remarks, 
                created_at, is_deleted, is_promoted, is_detained, is_dropout, is_passout, is_certified
            ) VALUES (
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, 
                NOW(), 0, 0, 0, 0, 0, 0
            )";

      $stmt = $pdo->prepare($sql);

      $headerFound = false; // Flag for finding the real data start

      while (($col = fgetcsv($handle, 10000, ",")) !== FALSE) {

        // Step A: Look for the real Header Row "Reg No"
        if (trim($col[0]) == 'Reg No') {
          $headerFound = true;
          continue; // Skip the header itself
        }

        // Step B: Ignore metadata rows (starting with #) or if header not found yet
        if (!$headerFound || empty($col[0]) || strpos($col[0], '#') === 0) {
          continue;
        }

        // Step C: Process Actual Data
        $adm_date = (!empty($col[1])) ? date('Y-m-d', strtotime($col[1])) : date('Y-m-d');
        $dob      = (!empty($col[9])) ? date('Y-m-d', strtotime($col[9])) : NULL;

        // Logic: Transport Route ID
        $transport_val = trim($col[29]);
        $route_id      = (strtolower($transport_val) == 'yes' && !empty($col[30])) ? $col[30] : NULL;

        $stmt->execute([
          strtoupper($col[0]), // 0. Reg No
          $adm_date,           // 1. Adm Date
          $col[2],             // 2. Session
          $col[3],             // 3. Class
          $col[4],             // 4. Section
          strtoupper($col[5]), // 5. Medium
          $col[6],             // 6. Group
          strtoupper($col[7]), // 7. Name
          $col[8],             // 8. CNIC
          $dob,                // 9. DOB
          strtoupper($col[10]), // 10. Gender
          strtoupper($col[11]), // 11. Lang
          strtoupper($col[12]), // 12. Caste
          strtoupper($col[13]), // 13. Tehsil
          strtoupper($col[14]), // 14. District
          $col[15],            // 15. Contact
          strtoupper($col[16]), // 16. Address
          strtoupper($col[17]), // 17. G Name
          $col[18],            // 18. Relation
          strtoupper($col[19]), // 19. Occupation
          $col[20],            // 20. G CNIC
          $col[21],            // 21. G Contact
          strtoupper($col[22]), // 22. G Address
          strtoupper($col[23]), // 23. Prev School
          $col[24],            // 24. Last Class
          $col[25],            // 25. Passing Year
          strtoupper($col[26]), // 26. Board
          $col[27],            // 27. Disability
          $col[28],            // 28. Hafiz
          $transport_val,      // 29. Transport
          $route_id,           // 30. Route ID
          $col[31],            // 31. Interests
          $col[32]             // 32. Remarks
        ]);
        $count++;
      }
      $pdo->commit();
      $success = true;
    }
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $error = "Import Error: " . $e->getMessage();
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
                        <small class="text-muted mt-2 d-block">System will ignore Reference Data and find "Reg No" automatically.</small>
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