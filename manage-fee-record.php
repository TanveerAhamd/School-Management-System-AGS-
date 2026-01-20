<!DOCTYPE html>
<html lang="en">


<!-- forms-validation.html  21 Nov 2019 03:55:16 GMT -->

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Manage Fee Record</title>
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
                        <h5 class="page-title mb-0">Manage Fee Record</h5>
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
                              <i class="fas fa-list"></i> Fee Record
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
                    <p class="mb-0 font-weight-bold">
                      <i class="fa fa-list"></i>&nbsp;Students Fee Collection Records
                    </p>
                  </div>

                  <div class="card-body border-bottom">
                    <div class="row">
                      <div class="col-md-3">
                        <select class="form-control select2">
                          <option>All Fee Types</option>
                          <option>Registration Fee</option>
                          <option>Tution Fee</option>
                          <option>Examination Fee</option>
                          <option>Stationary Fund</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <select class="form-control select2">
                          <option>Select Class</option>
                          <option>Class 5</option>
                          <option>Class 6</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <select class="form-control select2">
                          <option>Select Section</option>
                          <option>Section A</option>
                          <option>Section B</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <select class="form-control select2">
                          <option>Status</option>
                          <option>Paid</option>
                          <option>Unpaid</option>
                        </select>
                      </div>


                      <div class="col-12">
                        <label>&nbsp;</label>
                        <button class="btn btn-sm btn-primary btn-block">
                          <i class="fa fa-search"></i> Fetch Results
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Invoice No</th>
                            <th>Student Name</th>
                            <th>Class Name</th>
                            <th>Section</th>
                            <th>Fee Type</th>
                            <th>Total Amount</th>
                            <th>Amount Paid</th>
                            <th>Remaining</th>
                            <th>Status</th>

                          </tr>
                        </thead>
                        <tbody>
                          <!-- Student 1 -->
                          <tr>
                            <td>1</td>
                            <td>2026-01-10</td>
                            <td>INV-001</td>
                            <td>Ali Khan</td>
                            <td>9th</td>
                            <td>Section-A</td>
                            <td>
                              <div class="d-flex gap-1 text-white">
                                <span class="d-inline-block badge bg-success small" title="Admission Fee Paid">A</span>
                                <span class="d-inline-block badge bg-danger small"
                                  title="Registration Fee Unpaid">R</span>
                                <span class="d-inline-block badge bg-success small" title="Tuition Fee Paid">T</span>
                                <span class="d-inline-block badge bg-danger small"
                                  title="Stationary Fund Unpaid">S</span>
                              </div>
                            </td>


                            <td>10000</td>
                            <td>6000</td>
                            <td>4000</td>
                            <td><span class="badge badge-warning">Partial Paid</span></td>

                          </tr>

                          <!-- Student 2 -->
                          <tr>
                            <td>2</td>
                            <td>2026-01-11</td>
                            <td>INV-002</td>
                            <td>Ahmad</td>
                            <td>9th</td>
                            <td>Section-A</td>
                            <td>
                              <div class="d-flex gap-1 text-white">
                                <span class="d-inline-block badge bg-success small" title="Admission Fee Paid">A</span>
                                <span class="d-inline-block badge bg-danger small"
                                  title="Registration Fee Unpaid">R</span>
                                <span class="d-inline-block badge bg-success small" title="Tuition Fee Paid">T</span>
                                <span class="d-inline-block badge bg-danger small"
                                  title="Stationary Fund Unpaid">S</span>
                              </div>
                            </td>
                            <td>7000</td>
                            <td>0</td>
                            <td>7000</td>
                            <td><span class="badge badge-success"> Full Paid</span></td>

                          </tr>
                          <tr>
                            <td>2</td>
                            <td>2026-01-11</td>
                            <td>INV-002</td>
                            <td>Ahmad</td>
                            <td>9th</td>
                            <td>Section-A</td>
                            <td>
                              <div class="d-flex gap-1 text-white">
                                <span class="d-inline-block badge bg-success small" title="Admission Fee Paid">A</span>
                                <span class="d-inline-block badge bg-danger small"
                                  title="Registration Fee Unpaid">R</span>
                                <span class="d-inline-block badge bg-success small" title="Tuition Fee Paid">T</span>
                                <span class="d-inline-block badge bg-danger small"
                                  title="Stationary Fund Unpaid">S</span>
                              </div>
                            </td>
                            <td>7000</td>
                            <td>0</td>
                            <td>7000</td>
                            <td><span class="badge badge-secondary">Left</span></td>

                          </tr>
                          <!-- Student 2 -->
                          <tr>
                            <td>2</td>
                            <td>2026-01-11</td>
                            <td>INV-002</td>
                            <td>Ahmad</td>
                            <td>9th</td>
                            <td>Section-A</td>
                            <td>
                              <div class="d-flex gap-1 text-white">
                                <span class="d-inline-block badge bg-success small" title="Admission Fee Paid">A</span>
                                <span class="d-inline-block badge bg-danger small"
                                  title="Registration Fee Unpaid">R</span>
                                <span class="d-inline-block badge bg-success small" title="Tuition Fee Paid">T</span>
                                <span class="d-inline-block badge bg-danger small"
                                  title="Stationary Fund Unpaid">S</span>
                              </div>
                            </td>
                            <td>7000</td>
                            <td>0</td>
                            <td>7000</td>
                            <td><span class="badge badge-danger">Unpaid</span></td>

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

      <!-- Collect Fee Modal -->
      <div class="modal fade" id="collectFeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">

            <!-- modal header -->
            <div class="modal-header border-bottom">
              <h5 class="modal-title font-weight-bold">
                <i class="fa fa-user"></i>&nbsp;Collect Fee (Single Student)
              </h5>
              <button type="button" class="close" data-dismiss="modal">
                <span>&times;</span>
              </button>
            </div>

            <!-- form -->
            <form class="needs-validation" novalidate>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Invoice Number</label>
                      <input type="text" class="form-control read-only" value="INV-2026-001" required>
                      <div class="invalid-feedback">Invoice number required</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Class</label>
                      <select class="form-control select2" required>
                        <option value="">Select Class</option>
                        <option>Grade 1</option>
                        <option>Grade 2</option>
                      </select>
                      <div class="invalid-feedback">Select class</div>
                    </div>
                  </div>
                  <div class="col-md-6">

                    <div class="form-group">
                      <label>Student</label>
                      <select class="form-control select2" required>
                        <option value="">Select Student</option>
                        <option>Ali Khan</option>
                        <option>Ayesha Noor</option>
                      </select>
                      <div class="invalid-feedback">Select student</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Fee Type</label>
                      <select class="form-control select2" required>
                        <option value="">Select Fee Type</option>
                        <option>Tuition Fee</option>
                        <option>Stationary Fund</option>
                        <option>Admission Fee</option>
                        <option>Registration Fee</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Amount</label>
                      <input type="number" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Discount (%)</label>
                      <input type="number" class="form-control" value="0">
                    </div>
                  </div>
                  <div class="col-md-6">

                    <div class="form-group">
                      <label>Payment Date</label>
                      <input type="date" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Payment Status</label>
                      <select class="form-control select2" required>
                        <option value="">Select Status</option>
                        <option>Paid</option>
                        <option>Unpaid</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Payment Method</label>
                      <select class="form-control select2" required>
                        <option value="">Select Method</option>
                        <option>Cash</option>
                        <option>Card</option>
                        <option>Cheque</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Description</label>
                      <textarea class="form-control" rows="2"></textarea>
                    </div>
                  </div>
                </div>

              </div>

              <!-- modal footer -->
              <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                  Cancel
                </button>
                <button type="submit" class="btn btn-info btn-sm">
                  <i class="fa fa-save"></i>&nbsp;Collect Fee
                </button>
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