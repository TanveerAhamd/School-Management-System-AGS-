<!DOCTYPE html>
<html lang="en">


<!-- forms-validation.html  21 Nov 2019 03:55:16 GMT -->

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Pay Fee</title>
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
                        <h5 class="page-title mb-0">Fee Collection</h5>
                      </div>
                      <!-- RIGHT SIDE -->
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item">
                              <a href="#"><i class="fas fa-tachometer-alt"></i> Home</a>
                            </li>
                            <li class="breadcrumb-item">
                              <a href="#"><i class="far fa-file"></i> Fee Management</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                              <i class="fas fa-list"></i> pay Fee
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

                  <!-- HEADER -->
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold">
                      <i class="fa fa-filter"></i>&nbsp; Filter Students to Pay Fee
                    </p>
                  </div>

                  <!-- FILTER -->
                  <div class="card-body border-bottom">
                    <div class="row">
                      <div class="col-md-3">
                        <select class="form-control select2">
                          <option>All Classes</option>
                          <option>9th</option>
                          <option>10th</option>
                        </select>
                      </div>

                      <div class="col-md-3">
                        <select class="form-control select2">
                          <option>All Sections</option>
                          <option>A</option>
                          <option>B</option>
                        </select>
                      </div>

                      <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search Student Name">
                      </div>

                      <div class="col-md-2">
                        <button class="btn btn-primary btn-block">
                          <i class="fa fa-search"></i> Filter
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- STUDENT LIST -->
                  <div class="card-body table-responsive">
                    <table class="table table-striped table-hover table-sm" id="tableExport" style="width:100%;">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Name</th>
                          <th>Father Name</th>
                          <th>Class</th>
                          <th>Section</th>
                          <th>Pending Fee</th>
                          <th>Action</th>
                        </tr>
                      </thead>

                      <tbody>
                        <!-- Student 1 -->
                        <tr>
                          <td>1</td>
                          <td>Ali Khan</td>
                          <td>Ali Khan</td>
                          <td>9th</td>
                          <td>A</td>
                          <td><span class="badge badge-danger">4000</span></td>
                          <td>
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#collectFeeModal">
                              <i class="fa fa-plus"></i> Collect
                            </button>
                          </td>
                        </tr>

                        <!-- Student 2 -->
                        <tr>
                          <td>2</td>
                          <td>Ahmad</td>
                          <td>Ahmad</td>
                          <td>9th</td>
                          <td>B</td>
                          <td><span class="badge badge-warning">2000</span></td>
                          <td>
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#collectFeeModal">
                              <i class="fa fa-plus"></i> Collect
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
      <!-- include footer form include folder -->
      <?php include 'include/footer.php'; ?>

      <!-- Collect pay fee  Modal -->
      <div class="modal fade" id="collectFeeModal">
        <div class="modal-dialog modal-md">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                <i class="fa fa-money"></i> Collect Fee
              </h5>
              <button class="close" data-dismiss="modal">&times;</button>
            </div>

            <form>
              <div class="modal-body">

                <!-- AUTO FILLED INFO -->
                <div class="form-group">
                  <label>Invoice No</label>
                  <input type="text" class="form-control" value="INV-2026-004" readonly>
                </div>

                <div class="form-group">
                  <label>Student</label>
                  <input type="text" class="form-control" value="Ali Khan" readonly>
                </div>

                <div class="form-group">
                  <label>Class / Section</label>
                  <input type="text" class="form-control" value="9th - A" readonly>
                </div>

                <!-- FEE INPUT -->
                <div class="form-group">
                  <label>Fee Type</label>
                  <select class="form-control select2">
                    <option>Tuition Fee</option>
                    <option>Admission Fee</option>
                    <option>Registration Fee</option>
                    <option>Stationary Fund</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Amount</label>
                  <input type="number" class="form-control">
                </div>

                <div class="form-group">
                  <label>Status</label>
                  <select class="form-control">
                    <option>Paid</option>
                    <option>Partial Paid</option>
                  </select>
                </div>

              </div>

              <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                  Cancel
                </button>

                <div>
                  <button type="button" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-print"></i> Print Invoice
                  </button>

                  <button type="submit" class="btn btn-info btn-sm">
                    <i class="fa fa-save"></i> Save
                  </button>
                </div>
              </div>
            </form>

          </div>
        </div>
      </div>
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