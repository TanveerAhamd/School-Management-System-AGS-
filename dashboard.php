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
          <div class="row ">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
              <div class="card">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row ">
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                        <div class="card-content">
                          <h5 class="font-15">New Admissions</h5>
                          <h2 class="mb-3 font-18">124</h2>
                          <p class="mb-0"><span class="col-green">12%</span> This Month</p>
                        </div>
                      </div>
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                        <div class="banner-img">
                          <img src="assets/img/banner/1.png" alt="Students">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
              <div class="card">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row ">
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                        <div class="card-content">
                          <h5 class="font-15"> Attendance</h5>
                          <h2 class="mb-3 font-18">94%</h2>
                          <p class="mb-0"><span class="col-orange">02%</span> Today's Absent</p>
                        </div>
                      </div>
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                        <div class="banner-img">
                          <img src="assets/img/banner/2.png" alt="Attendance">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
              <div class="card">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row ">
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                        <div class="card-content">
                          <h5 class="font-15">Total Faculty</h5>
                          <h2 class="mb-3 font-18">85</h2>
                          <p class="mb-0"><span class="col-green">Active</span> 5 On Leave</p>
                        </div>
                      </div>
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                        <div class="banner-img">
                          <img src="assets/img/banner/3.png" alt="Staff">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
              <div class="card">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row ">
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                        <div class="card-content">
                          <h5 class="font-15">Fee Collection</h5>
                          <h2 class="mb-3 font-18">$15,450</h2>
                          <p class="mb-0"><span class="col-green">85%</span> Paid (Current)</p>
                        </div>
                      </div>
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                        <div class="banner-img">
                          <img src="assets/img/banner/4.png" alt="Revenue">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
              <div class="card">
                <div class="card-header">
                  <h4>Fee Collection vs Expenses Chart</h4>
                  <div class="card-header-action">
                    <div class="dropdown">
                      <a href="#" data-toggle="dropdown" class="btn btn-warning dropdown-toggle">Reports</a>
                      <div class="dropdown-menu">
                        <a href="#" class="dropdown-item has-icon"><i class="fas fa-eye"></i> Class-wise</a>
                        <a href="#" class="dropdown-item has-icon"><i class="far fa-edit"></i> Monthly</a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item has-icon text-danger"><i class="far fa-trash-alt"></i>
                          Delete</a>
                      </div>
                    </div>
                    <a href="#" class="btn btn-primary">Download PDF</a>
                  </div>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-lg-9">
                      <div id="chart1"></div>
                      <div class="row mb-0">
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                          <div class="list-inline text-center">
                            <div class="list-inline-item p-r-30"><i data-feather="arrow-up-circle"
                                class="col-green"></i>
                              <h5 class="m-b-0">$2,450</h5>
                              <p class="text-muted font-14 m-b-0">Monthly Salary</p>
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                          <div class="list-inline text-center">
                            <div class="list-inline-item p-r-30"><i data-feather="arrow-down-circle"
                                class="col-orange"></i>
                              <h5 class="m-b-0">$1,200</h5>
                              <p class="text-muted font-14 m-b-0">Utility Bills</p>
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                          <div class="list-inline text-center">
                            <div class="list-inline-item p-r-30"><i data-feather="arrow-up-circle"
                                class="col-green"></i>
                              <h5 class="mb-0 m-b-0">$45,000</h5>
                              <p class="text-muted font-14 m-b-0">Annual Profit</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="row mt-5">
                        <div class="col-7 col-xl-7 mb-3">Total Students</div>
                        <div class="col-5 col-xl-5 mb-3">
                          <span class="text-big">2,150</span>
                          <sup class="col-green">+05%</sup>
                        </div>
                        <div class="col-7 col-xl-7 mb-3">Total Teachers</div>
                        <div class="col-5 col-xl-5 mb-3">
                          <span class="text-big">85</span>
                          <sup class="text-muted">Active</sup>
                        </div>
                        <div class="col-7 col-xl-7 mb-3">Active Classes</div>
                        <div class="col-5 col-xl-5 mb-3">
                          <span class="text-big">32</span>
                          <sup class="col-green">OK</sup>
                        </div>
                        <div class="col-7 col-xl-7 mb-3">Pending Fees</div>
                        <div class="col-5 col-xl-5 mb-3">
                          <span class="text-big">$4,200</span>
                          <sup class="text-danger">Alert</sup>
                        </div>
                      </div>
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

          <div class="row">
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
  <script src="assets/bundles/apexcharts/apexcharts.min.js"></script>
  <!-- Page Specific JS File -->
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  <!-- Page Specific JS File -->
  <script src="assets/js/page/sweetalert.js"></script>
  <script src="assets/js/page/index.js"></script>
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <!-- Custom JS File -->
  <script src="assets/js/custom.js"></script>
</body>


<!-- dashboard.php  21 Nov 2019 03:47:04 GMT -->

</html>