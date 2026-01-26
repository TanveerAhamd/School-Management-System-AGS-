<?php
session_start();
require 'db.php';

// Agar admin pehle se login hai, toh seedha dashboard pe bhej do
if (isset($_SESSION['admin_id'])) {
  header("Location: dashboard.php");
  exit();
}

$status = "";
$status_title = "";
$status_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  if (!empty($email) && !empty($password)) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
      $_SESSION['admin_id'] = $admin['id'];
      $_SESSION['admin_name'] = $admin['full_name'];
      $_SESSION['admin_email'] = $admin['email'];

      $status = "Aapka login kamyabi se mukammal ho gaya hai.";
      $status_title = "خوش آمدید!";
      $status_type = "success";
    } else {
      $status = "Email Or Password Incorrect.";
      $status_title = "Login Nakama!";
      $status_type = "error";
    }
  } else {
    $status = "Please fill all fields.";
    $status_title = "Fields Khali Hain!";
    $status_type = "info";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Login - AGS Management System</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
            <div class="card card-primary">
              <div class="align-items-center card-header d-flex flex-column justify-content-center">
                <img src="./assets/img/AGHS Logo.png" alt="Logo" class="img-fluid mb-2" style="max-height: 80px;">
                <h4>Login to Dashboard</h4>
              </div>
              <div class="card-body">
                <form method="POST" action="index.php" class="needs-validation" novalidate="">
                  <div class="form-group">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" class="form-control" name="email" tabindex="1" required autofocus>
                  </div>
                  <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" class="form-control" name="password" tabindex="2" required>
                  </div>
                  <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Login</button>
                  </div>
                </form>
                <div class="mt-4 text-center">
                  Account nahi hai? <a href="register_admin.php">Naya Account Banayein</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  <script src="assets/js/scripts.js"></script>

  <script>
    $(document).ready(function() {
      // 1. Session Timeout ya Logout ka Alert (Check Session)
      <?php if (isset($_SESSION['status'])): ?>
        swal({
          title: '<?php echo $_SESSION['status_title']; ?>',
          text: '<?php echo $_SESSION['status']; ?>',
          icon: '<?php echo $_SESSION['status_type']; ?>',
          timer: 3000,
          buttons: false,
        });
        <?php
        unset($_SESSION['status']);
        unset($_SESSION['status_title']);
        unset($_SESSION['status_type']);
        ?>
      <?php endif; ?>

      // 2. Login Form Submission ka Alert (Check $status)
      <?php if ($status != ""): ?>
        swal({
          title: '<?php echo $status_title; ?>',
          text: '<?php echo $status; ?>',
          icon: '<?php echo $status_type; ?>',
          timer: 2000,
          buttons: false,
        }).then(function() {
          <?php if ($status_type == "success"): ?>
            window.location.href = "dashboard.php";
          <?php endif; ?>
        });
      <?php endif; ?>
    });
  </script>
</body>

</html>