<!DOCTYPE html>
<html lang="en">


<!-- forms-validation.html  21 Nov 2019 03:55:16 GMT -->

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Student List</title>
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
      <!-- Main Content -->
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
                        <h5 class="page-title mb-0">List Students</h5>
                      </div>
                      <!-- RIGHT SIDE -->
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item">
                              <a href="#"><i class="fas fa-tachometer-alt"></i> Home</a>
                            </li>
                            <li class="breadcrumb-item">
                              <a href="#"><i class="far fa-file"></i> Student Management</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                              <i class="fas fa-list"></i> List Students
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
              <!-- filter -->
              <div class="col-12">
                <div class="card">

                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold"><i class="fa fa-users"></i>&nbsp;Students</p>
                  </div>

                  <div class="card-body border-bottom">
                    <div class="row ">
                      <div class="col-md-4">
                        <select class="form-control select2 mb-1">
                          <option>Select Class</option>
                          <option>Class 5</option>
                          <option>Class 6</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <select class="form-control select2 mb-1">
                          <option>Select Section</option>
                          <option>Section A</option>
                          <option>Section B</option>
                        </select>
                      </div>

                      <div class="col-md-4">
                        <button class="btn btn-sm btn-primary btn-block py-2">
                          <i class="fa fa-search"></i> Fetch Students
                        </button>
                      </div>
                    </div>
                  </div>
                  <!-- data table -->
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
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
                          <!-- Student 1 -->
                          <tr>
                            <td>1</td>
                            <td>REG-2026-001</td>

                            <td>
                              <img src="assets/img/students/user-1.png" class="rounded-circle" width="35" height="35">
                            </td>

                            <td>Ali Khan</td>
                            <td>9th</td>
                            <td>A</td>
                            <td>
                              <div class="d-flex align-items-center gap-1 flex-nowrap">

                                <!-- View -->
                                <a href="student-detail-page.php" class="btn btn-success btn-circle btn-xs"
                                  title="View Student">
                                  <i class="fa fa-eye"></i>
                                </a>

                                <!-- Edit -->
                                <a href="student-edit-page.php" class="btn btn-info btn-circle btn-xs"
                                  title="Edit Student">
                                  <i class="fas fa-pencil-alt"></i>
                                </a>

                                <!-- Print -->
                                <a href="#" class="btn btn-warning btn-circle btn-xs" title="Print Student">
                                  <i class="fa fa-print"></i>
                                </a>

                                <!-- Delete -->
                                <a href="#" class="btn btn-danger btn-circle btn-xs" title="Delete Student">
                                  <i class="fa fa-times"></i>
                                </a>

                              </div>
                            </td>
                          </tr>

                          <!-- Student 2 -->
                          <tr>
                            <td>2</td>
                            <td>REG-2026-002</td>

                            <td>
                              <img src="assets/img/students/user-2.png" class="rounded-circle" width="35" height="35">
                            </td>

                            <td>Ahmad</td>
                            <td>9th</td>
                            <td>B</td>

                            <td>
                              <div class="d-flex align-items-center gap-1 flex-nowrap">

                                <!-- View -->
                                <a href="student-detail-page.php" class="btn btn-success btn-circle btn-xs"
                                  title="View Student">
                                  <i class="fa fa-eye"></i>
                                </a>

                                <!-- Edit -->
                                <a href="student-edit-page.php" class="btn btn-info btn-circle btn-xs"
                                  title="Edit Student">
                                  <i class="fas fa-pencil-alt"></i>
                                </a>

                                <!-- Print -->
                                <a href="#" class="btn btn-warning btn-circle btn-xs" title="Print Student">
                                  <i class="fa fa-print"></i>
                                </a>

                                <!-- Delete -->
                                <a href="#" class="btn btn-danger btn-circle btn-xs" title="Delete Student">
                                  <i class="fa fa-times"></i>
                                </a>

                              </div>
                            </td>
                          </tr>
                        </tbody>

                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <!-- data table -->
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
</body>


<!-- forms-validation.html  21 Nov 2019 03:55:16 GMT -->

</html>