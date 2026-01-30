<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Student Detail Page</title>

  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">


  <style>
    /* Screen pe metadata chhupane ke liye */
    .print-metadata {
      display: none;
    }

    /* --- PRINT FIX (NO OVERLAP & NO TOP SPACING) --- */
    @media print {

      /* 1. Page Setup */
      @page {
        size: A4;
        margin: 10mm;
      }

      body,
      html {
        margin: 0 !important;
        padding: 0 !important;
      }

      .main-content,
      .section,
      .main-wrapper,
      #app {
        padding: 0 !important;
        margin-top: 10px !important;
        margin-left: 10px !important;
        margin-right: 20px !important;
        display: block !important;
      }

      /* 3. Hide everything except printable area */
      body * {
        visibility: hidden;
      }

      #printableCard,
      #printableCard *,
      #page2,
      #page2 *,
      #page3,
      #page3 *,
      .print-metadata,
      .print-metadata * {
        visibility: visible !important;
      }

      /* Page Break Logic */
      #page2,
      #page3 {
        page-break-before: always;
        display: block !important;
      }

      .print-metadata {
        display: flex !important;
        justify-content: space-between;
        font-size: 11px;
        font-weight: bold;
        border-bottom: 1px solid #333;
        margin-bottom: 5px;
        padding-bottom: 2px;
      }

      #printableCard {
        position: relative !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        display: block !important;
      }

      .no-print,
      .bg-title,
      .main-footer,
      button {
        display: none !important;
      }

      .row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
      }

      .col-md-3 {
        width: 25% !important;
        flex: 0 0 25% !important;
      }

      .col-md-2 {
        width: 16.66% !important;
        flex: 0 0 16.66% !important;
      }

      .col-md-8 {
        width: 66.66% !important;
        flex: 0 0 66.66% !important;
      }

      .col-md-4 {
        width: 33.33% !important;
        flex: 0 0 33.33% !important;
      }

      .col-md-5 {
        width: 41.6% !important;
        flex: 0 0 41.6% !important;
      }

      .col-md-6 {
        width: 50% !important;
        flex: 0 0 50% !important;
      }

      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      .card-body.rounded {
        border: 4px solid #333 !important;
        padding: 20px !important;
        min-height: 275mm !important;
      }
    }

    /* Style for image containers */
    .img-box {
      border: 2px dashed #333;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f9f9f9;
      margin-top: 10px;
    }

    .img-box img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }
  </style>
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
          <!-- breadcrumb -->
          <div class="row bg-title">
            <div class="col-12">
              <div class="card mb-3">
                <div class="card-body py-2 b-0">
                  <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <!-- LEFT SIDE -->
                    <div class="mb-2 mb-md-0">
                      <h5 class="page-title mb-0">Student Detail view</h5>
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
                            <i class="fas fa-list"></i> Student Detail
                          </li>
                        </ol>
                      </nav>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card p-2 p-md-4 border-0" id="printableCard">
            <div class="print-metadata">
              <span style="color: #000; font-weight: bold;">Student Name: Fatima Ahmad</span>
              <span style="color: #000; font-weight: bold;">Print Date: 14-Jan-2026</span>
            </div>

            <div class="card-body rounded" style="border: 5px solid #0000004c !important ">
              <div class="row align-items-center mb-4">
                <div class="col-md-2 text-center">
                  <img src="./assets/img/agslogo.png" alt="Logo" style="width: 80px;">
                  <div class="mt-2">
                    <small class="fw-bold d-block">Reg #: <span class="text-primary"
                        style="color: #000 !important; font-weight: 900 !important;">AGDC-1024</span></small>
                    <small class="fw-bold">Date: <span
                        style="color: #000 !important; font-weight: 900 !important;">14-Jan-2026</span></small>
                  </div>
                </div>

                <div class="col-md-8 text-center">
                  <h2 class="m-0 fw-bold">Amina Girls Degree College</h2>
                  <p class="m-0">Gailywal 21-MPR lodhran</p>
                  <h6 class="mt-2 rounded bg-primary px-3 py-1 d-inline-block text-white font-weight-bold">
                    STUDENT DETAIL RECORD
                  </h6>
                  <div class="d-flex justify-content-around mt-3 border-top border-bottom py-2">
                    <span><strong>Class:</strong> <span
                        style="color: #000 !important; font-weight: 900 !important;">10th</span></span>
                    <span><strong>Section:</strong> <span
                        style="color: #000 !important; font-weight: 900 !important;">A</span></span>
                    <span><strong>Session:</strong> <span
                        style="color: #000 !important; font-weight: 900 !important;">2024-26</span></span>
                    <span><strong>Group:</strong> <span
                        style="color: #000 !important; font-weight: 900 !important;">ICS</span></span>
                  </div>
                </div>

                <div class="col-md-2 text-center">
                  <img src="assets/img/tanni.jpg" class="img-thumbnail"
                    style="height: 120px; width: 110px; object-fit: cover;">
                </div>
              </div>

              <div class="mt-3 mb-4 text-center">
                <div class="badge bg-light text-dark p-2 border w-100">
                  <strong>Subjects:</strong> <span style="color: #000 !important; font-weight: 900 !important;">Math,
                    Physics, Chemistry, Computer Science</span>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-12 d-flex justify-content-between border-bottom pb-1">
                  <h6 class="fw-bold"><i class='fas fa-user-circle'></i> Student Information</h6>
                  <h6 dir="rtl">نام (اردو): ___________________</h6>
                </div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Student Name</label><span
                    class="fw-bold text-uppercase" style="color: #000 !important; font-weight: 900 !important;">Fatima
                    Ahmad</span></div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">B-Form / CNIC</label><span
                    class="fw-bold" style="color: #000 !important; font-weight: 900 !important;">31101-1234567-0</span>
                </div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Date of Birth</label><span
                    class="fw-bold" style="color: #000 !important; font-weight: 900 !important;">12-Oct-2008</span>
                </div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Gender</label><span class="fw-bold"
                    style="color: #000 !important; font-weight: 900 !important;">Female</span></div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Mother Language</label><span
                    class="fw-bold" style="color: #000 !important; font-weight: 900 !important;">Urdu / Punjabi</span>
                </div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Caste</label><span class="fw-bold"
                    style="color: #000 !important; font-weight: 900 !important;">Rajput</span></div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Contact #</label><span
                    class="fw-bold" style="color: #000 !important; font-weight: 900 !important;">0300-1234567</span>
                </div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Address</label><span class="fw-bold"
                    style="color: #000 !important; font-weight: 900 !important;">Main City, Lodhran</span></div>
              </div>

              <div class="row mt-5">
                <div class="col-12 d-flex justify-content-between border-bottom pb-1">
                  <h6 class="fw-bold"><i class='fas fa-users'></i> Guardian Information</h6>
                  <h6 dir="rtl">والد کا نام (اردو): ___________________</h6>
                </div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Guardian Name</label><span
                    class="fw-bold" style="color: #000 !important; font-weight: 900 !important;">Ahmad Khan</span></div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Relation</label><span class="fw-bold"
                    style="color: #000 !important; font-weight: 900 !important;">Father</span></div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Occupation</label><span
                    class="fw-bold" style="color: #000 !important; font-weight: 900 !important;">Government
                    Employee</span></div>
                <div class="col-md-3 mt-3"><label class="d-block small text-muted">Contact #</label><span
                    class="fw-bold" style="color: #000 !important; font-weight: 900 !important;">0301-7654321</span>
                </div>
              </div>

              <div class="row rounded mt-3" style="background-color: rgba(223, 223, 223, 0.758);">
                <div class="col-12 px-3">
                  <h6 class="text-dark-subtle border-bottom pt-2">
                    <i class='fas fa-university'></i> Previous School Information
                  </h6>
                  <div class="row pb-3">
                    <div class="col-md-5 mt-2">
                      <label>School Name</label>
                      <input type="text" class="form-control bg-white text-dark fw-bold"
                        style="color: #000 !important; border-color: #000 !important;"
                        value="Govt. High School Gailywal, Lodhran" readonly>
                    </div>
                    <div class="col-md-2 mt-2">
                      <label>Last Class</label>
                      <input type="text" class="form-control bg-white text-dark fw-bold"
                        style="color: #000 !important; border-color: #000 !important;" value="9th" readonly>
                    </div>
                    <div class="col-md-2 mt-2">
                      <label>Passing Year</label>
                      <input type="text" class="form-control bg-white text-dark fw-bold"
                        style="color: #000 !important; border-color: #000 !important;" value="2024" readonly>
                    </div>
                    <div class="col-md-3 mt-2">
                      <label>Board Name</label>
                      <input type="text" class="form-control bg-white text-dark fw-bold"
                        style="color: #000 !important; border-color: #000 !important;" value="BISE Multan" readonly>
                    </div>

                    <div class="col-md-8 mt-3">
                      <div class="form-check d-flex align-items-center gap-2 mt-3">
                        <input class="form-check-input" type="checkbox" id="declareCheck"
                          style="width: 20px; height: 20px; pointer-events: none;" checked>
                        <label class="form-check-label fw-bold " for="declareCheck">
                          &nbsp; &nbsp; I declare that the information provided is correct.
                        </label>
                      </div>
                    </div>
                    <div class="col-md-4 text-end">
                      <div style="border-top: 1px solid #000; width: 200px; display: inline-block;" class="mt-5">
                        <p class="text-center fw-bold mb-0">Student Signature</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row rounded mt-3" style="background-color: rgba(223, 223, 223, 0.758);">
                <div class="col-12 px-3">
                  <h6 class="text-dark-subtle border-bottom pt-2">
                    <i class='fas fa-money-bill-wave'></i> Board Fee Record Information (9th/10th)
                  </h6>
                  <div class="row pb-3">
                    <div class="col-md-3 mt-2">
                      <label>Registration Fee</label>
                      <input type="text" class="form-control bg-white text-dark fw-bold"
                        style="color: #000 !important; border-color: #000 !important;" value="1000" readonly>
                    </div>
                    <div class="col-md-3 mt-2">
                      <label>Examination Fee</label>
                      <input type="text" class="form-control bg-white text-dark fw-bold"
                        style="color: #000 !important; border-color: #000 !important;" value="1800" readonly>
                    </div>
                    <div class="col-md-3 mt-2">
                      <label>Stationary Fund</label>
                      <input type="text" class="form-control bg-white text-dark fw-bold"
                        style="color: #000 !important; border-color: #000 !important;" value="500" readonly>
                    </div>
                    <div class="col-md-3 mt-2">
                      <label>Board Name</label>
                      <input type="text" class="form-control bg-white text-dark fw-bold"
                        style="color: #000 !important; border-color: #000 !important;" value="Multan Board" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mt-5 pt-3 rounded"
                style="background-color: #f8f9fa !important; border: 1px solid #ddd !important;">
                <div class="col-md-4">
                  <h6 class="fw-bold border-bottom pb-1">Office Use</h6>
                  <p class="mb-1"><strong>Disability:</strong> No</p>
                  <p class="mb-1"><strong>Transport:</strong> Yes (Route: Gailywal)</p>
                  <p class="mb-1"><strong>Interests:</strong> Reading, Sports</p>
                </div>
                <div class="col-md-8">
                  <h6 class="fw-bold border-bottom pb-1">Remarks / Authority Note</h6>
                  <div class="p-2 border bg-white rounded"
                    style="min-height: 80px; color: #000 !important; font-weight: bold;">
                    Student is eligible for merit scholarship based on previous results.
                  </div>
                  <div class="d-flex justify-content-end mt-5">

                    <div class="text-center" style="width: 150px; border-top: 1px solid #000;"><small>Administrator
                        Sign</small></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card p-2 p-md-4 border-0" id="page2" style="page-break-before: always;">
            <div class="card-body rounded" style="min-height: 290mm; border: 1px solid #ccc !important;">
              <h5 class="text-center fw-bold border-bottom pb-2">PAGE 02: PARENT CNIC & RESULT CARD</h5>
              <div class="row mt-4 text-center">
                <div class="col-md-6">
                  <h6 class="fw-bold small">GUARDIAN CNIC (FRONT)</h6>
                  <div class="img-box" style="height: 220px; border: 1px solid #eee;">
                    <img src="./assets/img/cnicf.jpg" class="img-fluid" style="max-height: 100%;" alt="Front CNIC">
                  </div>
                </div>
                <div class="col-md-6">
                  <h6 class="fw-bold small">GUARDIAN CNIC (BACK)</h6>
                  <div class="img-box" style="height: 220px; border: 1px solid #eee;">
                    <img src="./assets/img/cnicback.jpg" class="img-fluid" style="max-height: 100%;" alt="Back CNIC">
                  </div>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col-12">
                  <h6 class="fw-bold border-bottom pb-1">PREVIOUS RESULT CARD / DMC</h6>
                  <div class="img-box"
                    style="height: 700px; display: flex; align-items: start; justify-content: center;">
                    <img src="./assets/img/ICS result card copy tanveer.jpg" class="img-fluid"
                      style="max-height: 100%; width: auto;" alt="Result Card">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card p-2 p-md-4 border-0" id="page3" style="page-break-before: always;">
            <div class="card-body rounded" style="min-height: 290mm; border: 1px solid #ccc !important;">
              <h5 class="text-center fw-bold border-bottom pb-2">PAGE 03: STUDENT B-FORM</h5>
              <div class="row mt-4">
                <div class="col-12 text-center">
                  <div class="img-box"
                    style="height: 950px; display: flex; align-items: start; justify-content: center;">
                    <img src="./assets/img/Matric sanad color tanveer.jpg" class="img-fluid"
                      style="max-height: 100%; width: auto;" alt="B-Form">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="text-center mt-5 no-print mb-5">
            <button type="button" class="btn btn-success btn-lg px-5" onclick="window.print()">
              <i class="fas fa-print"></i> Print
            </button>
          </div>

        </section>
      </div>


      <!-- include footer form include folder -->
      <?php include 'include/footer.php'; ?>
    </div>
  </div>
  <!-- General JS Scripts -->
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

</html>