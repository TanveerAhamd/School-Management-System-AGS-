<?php require_once 'auth.php'; ?>

<!DOCTYPE html>
<html lang="en">



<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Dashboard</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/css/app.min.css">
  <!-- Template CSS -->
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


          <div class="row">
            <div class="col-xl-3 col-lg-6">
              <div class="card">
                <div class="card-bg">
                  <div class="chartjs-size-monitor" style="position: absolute; inset: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;">
                    <div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                      <div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div>
                    </div>
                    <div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                      <div style="position:absolute;width:200%;height:200%;left:0; top:0"></div>
                    </div>
                  </div>
                  <div class="p-t-20 d-flex justify-content-between">
                    <div class="col ps-3 pe-3">
                      <h6 class="mb-0">New Booking</h6>
                      <span class="fw-bold mb-0 font-20">1,562</span>
                    </div>
                    <i class="fas fa-address-card card-icon col-orange font-30 p-r-30"></i>
                  </div>
                  <canvas id="cardChart1" height="114" width="430" style="display: block; height: 57px; width: 215px;" class="chartjs-render-monitor"></canvas>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card">
                <div class="card-bg">
                  <div class="chartjs-size-monitor" style="position: absolute; inset: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;">
                    <div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                      <div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div>
                    </div>
                    <div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                      <div style="position:absolute;width:200%;height:200%;left:0; top:0"></div>
                    </div>
                  </div>
                  <div class="p-t-20 d-flex justify-content-between">
                    <div class="col ps-3 pe-3">
                      <h6 class="mb-0">New Customers</h6>
                      <span class="fw-bold mb-0 font-20">895</span>
                    </div>
                    <i class="fas fa-diagnoses card-icon col-green font-30 p-r-30"></i>
                  </div>
                  <canvas id="cardChart2" height="114" width="430" style="display: block; height: 57px; width: 215px;" class="chartjs-render-monitor"></canvas>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card">
                <div class="card-bg">
                  <div class="chartjs-size-monitor" style="position: absolute; inset: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;">
                    <div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                      <div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div>
                    </div>
                    <div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                      <div style="position:absolute;width:200%;height:200%;left:0; top:0"></div>
                    </div>
                  </div>
                  <div class="p-t-20 d-flex justify-content-between">
                    <div class="col ps-3 pe-3">
                      <h6 class="mb-0">Growth</h6>
                      <span class="fw-bold mb-0 font-20">+22.58%</span>
                    </div>
                    <i class="fas fa-chart-bar card-icon col-indigo font-30 p-r-30"></i>
                  </div>
                  <canvas id="cardChart3" height="114" width="430" style="display: block; height: 57px; width: 215px;" class="chartjs-render-monitor"></canvas>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6">
              <div class="card">
                <div class="card-bg">
                  <div class="chartjs-size-monitor" style="position: absolute; inset: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;">
                    <div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                      <div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div>
                    </div>
                    <div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                      <div style="position:absolute;width:200%;height:200%;left:0; top:0"></div>
                    </div>
                  </div>
                  <div class="p-t-20 d-flex justify-content-between">
                    <div class="col ps-3 pe-3">
                      <h6 class="mb-0">Revenue</h6>
                      <span class="fw-bold mb-0 font-20">$2,687</span>
                    </div>
                    <i class="fas fa-hand-holding-usd card-icon col-cyan font-30 p-r-30"></i>
                  </div>
                  <canvas id="cardChart4" height="114" width="430" style="display: block; height: 57px; width: 215px;" class="chartjs-render-monitor"></canvas>
                </div>
              </div>
            </div>
          </div>



          <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
              <div class="card card-statistic-1">
                <div class="card-icon l-bg-purple">
                  <i class="fas fa-cart-plus"></i>
                </div>
                <div class="card-wrap">
                  <div class="padding-20">
                    <div class="text-end">
                      <h3 class="font-light mb-0">
                        <i class="ti-arrow-up text-success"></i> 524
                      </h3>
                      <span class="text-muted">Order Received</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
              <div class="card card-statistic-1">
                <div class="card-icon l-bg-green">
                  <i class="fas fa-hiking"></i>
                </div>
                <div class="card-wrap">
                  <div class="padding-20">
                    <div class="text-end">
                      <h3 class="font-light mb-0">
                        <i class="ti-arrow-up text-success"></i> 158
                      </h3>
                      <span class="text-muted">New Clients</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
              <div class="card card-statistic-1">
                <div class="card-icon l-bg-cyan">
                  <i class="fas fa-chart-line"></i>
                </div>
                <div class="card-wrap">
                  <div class="padding-20">
                    <div class="text-end">
                      <h3 class="font-light mb-0">
                        <i class="ti-arrow-up text-success"></i> 785
                      </h3>
                      <span class="text-muted">New Orders</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
              <div class="card card-statistic-1">
                <div class="card-icon l-bg-orange">
                  <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="card-wrap">
                  <div class="padding-20">
                    <div class="text-end">
                      <h3 class="font-light mb-0">
                        <i class="ti-arrow-up text-success"></i> $5,263
                      </h3>
                      <span class="text-muted">Todays Income</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>










          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>Class Progress & Teacher Assignments</h4>
                  <div class="card-header-form">
                    <form>
                      <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search Class">
                        <div class="input-group-btn">
                          <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <tr>
                        <th>Class Name</th>
                        <th>Section Incharge</th>
                        <th>Syllabus Status</th>
                        <th>Mid-Term Date</th>
                        <th>Exam Status</th>
                        <th>Action</th>
                      </tr>
                      <tr>
                        <td>Grade 10 - Science</td>
                        <td>
                          <ul class="list-unstyled order-list m-b-0 m-b-0">
                            <li class="team-member team-member-sm"><img class="rounded-circle"
                                src="assets/img/users/user-8.png" title="Sir Ahmed"></li>
                            <li class="team-member team-member-sm"><img class="rounded-circle"
                                src="assets/img/users/user-9.png" title="Ms. Sara"></li>
                          </ul>
                        </td>
                        <td class="align-middle">
                          <div class="progress-text">75%</div>
                          <div class="progress" data-height="6">
                            <div class="progress-bar bg-success" data-width="75%"></div>
                          </div>
                        </td>
                        <td>2024-03-15</td>
                        <td>
                          <div class="badge badge-success">Scheduled</div>
                        </td>
                        <td><a href="#" class="btn btn-outline-primary">View Detail</a></td>
                      </tr>
                      <tr>
                        <td>Grade 8 - General</td>
                        <td>
                          <ul class="list-unstyled order-list m-b-0 m-b-0">
                            <li class="team-member team-member-sm"><img class="rounded-circle"
                                src="assets/img/users/user-1.png" title="Mr. Khan"></li>
                          </ul>
                        </td>
                        <td class="align-middle">
                          <div class="progress-text">40%</div>
                          <div class="progress" data-height="6">
                            <div class="progress-bar bg-danger" data-width="40%"></div>
                          </div>
                        </td>
                        <td>2024-03-20</td>
                        <td>
                          <div class="badge badge-warning">Delayed</div>
                        </td>
                        <td><a href="#" class="btn btn-outline-primary">View Detail</a></td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- <div class="row">
            <div class="col-md-6 col-lg-12 col-xl-6">
              <div class="card">
                <div class="card-header">
                  <h4>Complaints / Requests</h4>
                </div>
                <div class="card-body">
                  <div class="support-ticket media pb-1 mb-3">
                    <img src="assets/img/users/user-1.png" class="user-img mr-2" alt="">
                    <div class="media-body ml-3">
                      <div class="badge badge-pill badge-info mb-1 float-right">Leave</div>
                      <span class="font-weight-bold">Student: Ali Khan</span>
                      <a href="javascript:void(0)">Request for 2 days medical leave</a>
                      <p class="my-1">Sir, I have high fever, please grant me leave for...</p>
                      <small class="text-muted">Grade 9-A &nbsp;&nbsp; - 2 hours ago</small>
                    </div>
                  </div>
                </div>
                <a href="javascript:void(0)" class="card-footer card-link text-center small ">View All Requests</a>
              </div>
            </div>

            <div class="col-md-6 col-lg-12 col-xl-6">
              <div class="card">
                <div class="card-header">
                  <h4>Recent Fee Transactions</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead>
                        <tr>
                          <th>Student</th>
                          <th>Roll No</th>
                          <th>Month</th>
                          <th>Status</th>
                          <th>Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Hamza Abbasi</td>
                          <td>S-102</td>
                          <td>January</td>
                          <td><span class="text-success">Paid</span></td>
                          <td>$150</td>
                        </tr>
                        <tr>
                          <td>Sara Malik</td>
                          <td>S-504</td>
                          <td>January</td>
                          <td><span class="text-danger">Pending</span></td>
                          <td>$150</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12 col-sm-6 col-lg-3">
                <div class="card">
                  <div class="card-body text-center">
                    <div class="mb-2">Simple Sweet Alert</div>
                    <button class="btn btn-primary" id="swal-1">Launch</button>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-lg-3">
                <div class="card">
                  <div class="card-body text-center">
                    <div class="mb-2">Success Message</div>
                    <button class="btn btn-primary" id="swal-2">Launch</button>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-lg-3">
                <div class="card">
                  <div class="card-body text-center">
                    <div class="mb-2">Warning Message</div>
                    <button class="btn btn-primary" id="swal-3">Launch</button>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-lg-3">
                <div class="card">
                  <div class="card-body text-center">
                    <div class="mb-2">Info Message</div>
                    <button class="btn btn-primary" id="swal-4">Launch</button>
                  </div>
                </div>
              </div>
            </div>
          </div> -->
        </section>
      </div>

      <!-- include footer form include folder -->
      <?php include 'include/footer.php'; ?>
    </div>
  </div>
  <!-- General JS Scripts -->
  <script src="assets/js/app.min.js"></script>
  <!-- JS Libraies -->

  <script src="assets/bundles/chartjs/chart.min.js"></script>
  <script src="assets/bundles/apexcharts/apexcharts.min.js"></script>
  <script src="assets/bundles/owlcarousel2/dist/owl.carousel.min.js"></script>
  <!-- Page Specific JS File -->
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  <!-- Page Specific JS File -->
  <script src="assets/js/page/sweetalert.js"></script>
  <script src="assets/js/page/index.js"></script>
  <script src="assets/js/page/widget-data.js"></script>
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <!-- Custom JS File -->
  <script src="assets/js/custom.js"></script>
</body>


<!-- dashboard.php  21 Nov 2019 03:47:04 GMT -->

</html>