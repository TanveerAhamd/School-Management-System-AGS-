<?php
session_start();
require 'db.php'; // Database connection file

$status = "";
$status_title = "";
$status_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($full_name) && !empty($email) && !empty($password)) {

        // Check if email already exists
        $check_email = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $check_email->execute([$email]);

        if ($check_email->rowCount() > 0) {
            $status = "Pradan kiya gaya email address pehle se system mein mojud hai.";
            $status_title = "Email Maujood Hai!";
            $status_type = "warning";
        } else {
            // Password Hashing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Database Insertion
            try {
                $sql = "INSERT INTO admins (full_name, email, password) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);

                if ($stmt->execute([$full_name, $email, $hashed_password])) {
                    $status = "Admin account has been created successfully. Aap ab login kar sakte hain.";
                    $status_title = "Registration Done!";
                    $status_type = "success";
                }
            } catch (PDOException $e) {
                $status = "Database mein technical error ki wajah se registration nahi ho saki.";
                $status_title = "System Error!";
                $status_type = "error";
            }
        }
    } else {
        $status = "Kripya form ki sabhi fields ko sahi se bharein.";
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
    <title>Admin Registration - AGS System</title>
    <link rel="stylesheet" href="assets/css/app.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/components.css">
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
                                <img src="./assets/img/agslogo.png" alt="Logo" class="img-fluid mb-2" style="max-height: 80px;">
                                <h4>Admin Registration</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="register_admin.php" class="needs-validation" novalidate="">
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" class="form-control" name="full_name" placeholder="Enter Full Name" required autofocus>
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <input type="email" class="form-control" name="email" placeholder="admin@example.com" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Secure Password</label>
                                        <input type="password" class="form-control" name="password" placeholder="********" required>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                                            Create Account
                                        </button>
                                    </div>
                                </form>
                                <div class="mt-4 text-center text-small">
                                    Already have an account? <a href="index.php">Login here</a>
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

    <?php if ($status != ""): ?>
        <script>
            swal({
                title: '<?php echo $status_title; ?>',
                text: '<?php echo $status; ?>',
                icon: '<?php echo $status_type; ?>',
                timer: 2000, // 2 Seconds
                buttons: false,
                closeOnClickOutside: true
            }).then(function() {
                <?php if ($status_type == "success"): ?>
                    window.location.href = "index.php";
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
</body>

</html>