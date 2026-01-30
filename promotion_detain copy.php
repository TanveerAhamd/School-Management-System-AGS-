<!DOCTYPE html>
<html lang="en">


<!-- forms-validation.html  21 Nov 2019 03:55:16 GMT -->

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Promotion & Detain Students</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/css/app.min.css">
  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <!-- Custom style CSS -->
  <link rel="stylesheet" href="assets/css/custom.css">
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
            <!-- breadcrumb -->
            <div class="row bg-title">
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-body py-2 b-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">

                      <!-- LEFT SIDE -->
                      <div class="mb-2 mb-md-0">
                        <h5 class="page-title mb-0">Student promotion and detian</h5>
                      </div>

                      <!-- RIGHT SIDE -->
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item">
                              <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a>
                            </li>
                            <li class="breadcrumb-item">
                              <a href="#"><i class="far fa-file"></i> Student</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                              <i class="fas fa-list"></i> Promotion & Detain
                            </li>
                          </ol>
                        </nav>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fas fa-filter"></i>&nbsp;Select Class to Process</p>
                  </div>
                  <div class="card-body border-bottom bg-light-all my-2">
                    <div class="row mb-3">
                      <div class="col-md-2">
                        <label class="font-weight-bold">Current Session</label>
                        <select class="form-control select2">
                          <option>2024-2025</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label class="font-weight-bold">Current Class</label>
                        <select class="form-control select2">
                          <option>9th</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label class="font-weight-bold">Section</label>
                        <select class="form-control select2">
                          <option>A</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label class="font-weight-bold">Assign Session</label>
                        <select class="form-control select2">
                          <option>2025-26</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label class="font-weight-bold">Promote To Class</label>
                        <select class="form-control select2">
                          <option>10th</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label class="font-weight-bold">Section</label>
                        <select class="form-control select2">
                          <option>A</option>
                        </select>
                      </div>

                      <div class=" w-100 mx-3 my-3 d-flex align-items-end">
                        <button class="btn btn-primary btn-block w-100 py-2"><i class="fa fa-search"></i> Fetch Student
                          List</button>
                      </div>
                    </div>
                  </div>

                  <div class="card-body">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                      <h6 class="font-weight-bold text-muted">Select Students from List:</h6>
                      <div>
                        <button class="btn btn-danger mr-2"><i class="fas fa-undo"></i> Detain
                          Selected</button>
                        <button class="btn btn-success"><i class="fas fa-graduation-cap"></i> Promote Selected</button>
                      </div>
                    </div>
                    <!-- data table -->
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th><input type="checkbox" id="checkAll"></th>
                            <th>Reg#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <!-- Student 1 -->
                          <tr>
                            <td><input type="checkbox" class="student-checkbox"></td>
                            <td>REG-2026-001</td>
                            <td>
                              <img src="assets/img/students/user-1.png" class="rounded-circle" width="35" height="35">
                            </td>
                            <td>Ali Khan</td>
                            <td>9th</td>
                            <td>A</td>
                            <td>Promoted</td>
                          </tr>
                          <!-- Student 2 -->
                          <tr>
                            <td><input type="checkbox" class="student-checkbox"></td>
                            <td>REG-2026-002</td>
                            <td>
                              <img src="assets/img/students/user-2.png" class="rounded-circle" width="35" height="35">
                            </td>
                            <td>Ahmad</td>
                            <td>9th</td>
                            <td>B</td>
                            <td>Detained</td>
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
      <!-- footer  -->
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