<?php
session_start();
require 'db.php'; // Database connection (PDO)

// 1. اسکول کا لوگو فیچ کریں
$school_q = $pdo->query("SELECT logo FROM school_settings LIMIT 1")->fetch();
$logo_path = (!empty($school_q['logo']) && file_exists('uploads/' . $school_q['logo']))
  ? 'uploads/' . $school_q['logo']
  : 'assets/img/agslogo.png'; // اگر ڈیٹا بیس میں نہ ہو تو یہ ڈیفالٹ راستہ

// Agar admin pehle se login hai, toh seedha dashboard pe bhej do
if (isset($_SESSION['admin_id'])) {
  header("Location: dashboard.php");
  exit();
}

$status = "";
$status_title = "";
$status_type = "";

// 2. صرف فارم سبمٹ ہونے پر لاجک چلے گی
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

      $status = "Redirecting to dashbaord!.";
      $status_title = "خوش آمدید!";
      $status_type = "success";
    } else {
      $status = "Email Or Password Incorrect.";
      $status_title = "Try Again!";
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
  <title>Login | AGHS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/custom.css">
\  <link rel="icon" href="assets/img/favicon.png">
</head>

<body class="bg-light">
  <div class="loader"></div>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
            <div class="card card-primary shadow-lg">
              <div class="align-items-center card-header d-flex flex-column justify-content-center py-2 pt-3">
                <!-- متحرک لوگو یہاں سے آئے گا -->
                <img src="<?= $logo_path ?>" alt="School Logo" class="img-fluid mb-3" style="max-height: 100px; border-radius: 10px;">
                <h4 class=" font-weight-bold  text-muted px-2 rounded">AGHS LOGIN </h4>
              </div>
              <div class="card-body pt-0">
                <form method="POST" action="index.php" class="needs-validation" novalidate="">
                  <div class="form-group">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" class="form-control" name="email" tabindex="1" placeholder="Enter your email" required autofocus>
                  </div>
                  <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" class="form-control" name="password" tabindex="2" placeholder="Enter your password" required>
                  </div>
                  <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm">
                      <i class="fas fa-sign-in-alt"></i> Login Now
                    </button>
                  </div>
                </form>
                <div class="mt-2 text-muted text-center muted">
                  <span>Design and Developed by</span>
                  <span class="mx-2 text-secondary small ">Dev.AGHS(Tech-Team)</span>
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
      // 1. لاگ آؤٹ یا سیشن میسج (صرف تب چلے گا جب سیشن میں موجود ہو)
      <?php if (isset($_SESSION['status'])): ?>
        swal({
          title: '<?= $_SESSION['status_title']; ?>',
          text: '<?= $_SESSION['status']; ?>',
          icon: '<?= $_SESSION['status_type']; ?>',
          timer: 3000,
          buttons: false,
        });
        <?php
        unset($_SESSION['status']);
        unset($_SESSION['status_title']);
        unset($_SESSION['status_type']);
        ?>
      <?php endif; ?>

      // 2. لاگ ان فارم سبمٹ ہونے کا الرٹ (صرف POST پر چلے گا)
      <?php if ($status != ""): ?>
        swal({
          title: '<?= $status_title; ?>',
          text: '<?= $status; ?>',
          icon: '<?= $status_type; ?>',
          timer: 1500,
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