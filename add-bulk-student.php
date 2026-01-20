<!DOCTYPE html>
<html lang="en">


<!-- forms-validation.html  21 Nov 2019 03:55:16 GMT -->

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Add Bulk Students</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/css/app.min.css">
  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <!-- Custom style CSS -->
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <!-- include navbar form include folder -->
      <?php include 'include/navbar.php'; ?>

      <div class="main-sidebar sidebar-style-2">
        <!-- include asidebar form include folder -->
        <?php include 'include/asidebar.php'; ?>
      </div>
      <!-- main section -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="row bg-title">
              <div class="col-12">
                <div class="card mb-3 shadow-sm">
                  <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                      <div class="mb-2 mb-md-0">
                        <h5 class="page-title mb-0 font-weight-bold"> Bulk Student Admission</h5>
                      </div>
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0 text-small">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Student</a></li>
                            <li class="breadcrumb-item active">Bulk Admission</li>
                          </ol>
                        </nav>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row justify-content-center">
              <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                  <div class="card-header border-bottom d-flex justify-content-between">
                    <h4 class="text-dark font-weight-bold mb-0"> <i class="fas fa-file-import"></i> Import Student Data
                    </h4>
                    <a href="sample_admission_template.csv" class="btn btn-outline-success btn-sm font-weight-bold">
                      <i class="fas fa-download"></i> Download Sample CSV
                    </a>
                  </div>

                  <div class="card-body">
                    <form action="process_bulk_admission.php" method="POST" enctype="multipart/form-data">

                      <div class="alert alert-light border border-info mb-4">
                        <div class="alert-title text-info"><i class="fas fa-info-circle"></i> Instructions</div>
                        <ul class="mb-0 small text-dark">
                          <li>Pehle sample file download karein aur columns ko mat badlein.</li>
                          <li>Student Name aur Father Name zaroori fields hain.</li>
                          <li>System sirf <b>.csv</b> ya <b>.xlsx</b> files support karta hai.</li>
                        </ul>
                      </div>

                      <div class="row">
                        <div class="col-md-4 mb-3">
                          <label class="font-weight-bold">Target Class <span class="text-danger">*</span></label>
                          <select name="class_id" class="form-control select2" required>
                            <option value="">-- Select Class --</option>
                            <option value="1">9th</option>
                            <option value="2">10th</option>
                          </select>
                        </div>

                        <div class="col-md-4 mb-3">
                          <label class="font-weight-bold">Target Section <span class="text-danger">*</span></label>
                          <select name="section_id" class="form-control select2" required>
                            <option value="">-- Select Section --</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                          </select>
                        </div>

                        <div class="col-md-4 mb-3">
                          <label class="font-weight-bold">Admission Session <span class="text-danger">*</span></label>
                          <select name="session_id" class="form-control select2" required>
                            <option>2024-2025</option>
                            <option selected>2025-2026</option>
                          </select>
                        </div>

                        <div class="col-12 mb-4 mt-2">
                          <div class="form-group mb-0">
                            <label class="font-weight-bold">Choose Student File</label>
                            <div class="custom-file">
                              <input type="file" name="student_file" class="custom-file-input" id="customFile"
                                accept=".csv, .xlsx" required>
                              <label class="custom-file-label" for="customFile">Choose file (CSV or Excel)</label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="card-footer bg-whitesmoke text-center border-0 rounded">
                        <button type="submit" name="import_bulk"
                          class="btn btn-success btn-lg px-5 shadow font-weight-bold">
                          <i class="fas fa-upload"></i> Upload & Process Students
                        </button>
                        <a href="all_students.php" class="btn btn-secondary btn-lg px-4 ml-2">Cancel</a>
                      </div>

                    </form>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </section>
      </div>
      <!-- include footer form include folder -->
      <?php include 'include/footer.php'; ?>
    </div>
  </div>
  <!-- General JS Scripts -->
  <script src="assets/js/app.min.js"></script>
  <!-- JS Libraies -->
  <!-- Page Specific JS File -->
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.flash.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>
  <script src="assets/js/page/datatables.js"></script>
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <!-- Custom JS File -->
  <script src="assets/js/custom.js"></script>
  <script>
    $(document).ready(function() {
      // 1. Select All Checkbox Logic
      // Isko 'body' ya 'document' par is liye rakha hai takay agar DataTable page change kare to b kaam kare
      $(document).on('click', '#checkAll', function() {
        $('.student-checkbox').prop('checked', this.checked);
      });

      // 2. Individual Checkbox Logic
      $(document).on('click', '.student-checkbox', function() {
        if ($('.student-checkbox:checked').length == $('.student-checkbox').length) {
          $('#checkAll').prop('checked', true);
        } else {
          $('#checkAll').prop('checked', false);
        }
      });
    });
  </script>
</body>


<!-- forms-validation.html  21 Nov 2019 03:55:16 GMT -->

</html>