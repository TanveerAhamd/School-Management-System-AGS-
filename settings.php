<?php
date_default_timezone_set('Asia/Karachi');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth.php'; // اس میں PDO کنکشن پہلے سے موجود ہونا چاہیے

$target_dir = "uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$swal = null;
$redirect_to_logout = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --- 1. School Profile Update Logic ---
    if (isset($_POST['btn_update_school'])) {
        $school_name = trim($_POST['school_name']);
        $address     = trim($_POST['address']);
        $contact     = trim($_POST['contact']);
        $old_logo    = $_POST['old_logo'];
        $logo_name   = $old_logo;

        if (!empty($_FILES['logo']['name'])) {
            $logo_name = "logo_" . time() . "_" . basename($_FILES['logo']['name']);
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $logo_name)) {
                if (!empty($old_logo) && file_exists($target_dir . $old_logo)) {
                    unlink($target_dir . $old_logo);
                }
            }
        }

        // Check if settings exist (Row ID 1 is fixed for settings)
        $check_school = $pdo->query("SELECT id FROM school_settings LIMIT 1")->fetch();
        if (!$check_school) {
            $stmt = $pdo->prepare("INSERT INTO school_settings (school_name, address, contact, logo, id) VALUES (?, ?, ?, ?, 1)");
            $res = $stmt->execute([$school_name, $address, $contact, $logo_name]);
        } else {
            $stmt = $pdo->prepare("UPDATE school_settings SET school_name=?, address=?, contact=?, logo=? WHERE id=1");
            $res = $stmt->execute([$school_name, $address, $contact, $logo_name]);
        }

        if ($res) {
            $swal = ["title" => "Updated!", "text" => "School profile has been updated successfully.", "type" => "success"];
        }
    }

    // --- 2. Admin Account Update Logic ---
    if (isset($_POST['btn_update_admin'])) {
        $admin_id  = $_SESSION['admin_id'];
        $full_name = trim($_POST['full_name']);
        $new_pass  = $_POST['password'];
        $old_pic   = $_POST['old_pic'];
        $pic_name  = $old_pic;

        if (!empty($_FILES['profile_pic']['name'])) {
            $pic_name = "admin_" . time() . "_" . basename($_FILES['profile_pic']['name']);
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_dir . $pic_name)) {
                if (!empty($old_pic) && file_exists($target_dir . $old_pic)) {
                    unlink($target_dir . $old_pic);
                }
            }
        }

        if (!empty($new_pass)) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET full_name=?, password=?, profile_pic=? WHERE id=?");
            $res = $stmt->execute([$full_name, $hashed_pass, $pic_name, $admin_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE admins SET full_name=?, profile_pic=? WHERE id=?");
            $res = $stmt->execute([$full_name, $pic_name, $admin_id]);
        }

        if ($res) {
            $swal = ["title" => "Profile Saved!", "text" => "Account updated. Please log in again for security.", "type" => "success"];
            $redirect_to_logout = true;
        }
    }
}

// Fetch Current Data
$school = $pdo->query("SELECT * FROM school_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$admin_stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$admin_stmt->execute([$_SESSION['admin_id']]);
$admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>School Profile | AGHS Lodhran</title>
    <link rel="stylesheet" href="assets/css/app.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="icon" href="assets/img/favicon.png">
    <style>
        .preview-circle {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 4px solid #6777ef;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .preview-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-header h4 {
            font-weight: 700 !important;
            color: #6777ef !important;
        }
    </style>
</head>

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <?php include 'include/navbar.php'; ?>
            <div class="main-sidebar sidebar-style-2"><?php include 'include/asidebar.php'; ?></div>

            <div class="main-content">
                <section class="section">
                    <div class="section-header">
                        <h1>System Settings</h1>
                    </div>

                    <div class="section-body">
                        <div class="row">
                            <!-- Left: School Profile -->
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header border-bottom">
                                        <h4><i class="fas fa-university"></i> School Profile</h4>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="card-body">
                                            <div class="preview-circle" id="logoCircle">
                                                <?php if (!empty($school['logo']) && file_exists('uploads/' . $school['logo'])): ?>
                                                    <img src="uploads/<?= $school['logo'] ?>">
                                                <?php else: ?>
                                                    <i class="fas fa-school fa-3x text-muted"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="form-group">
                                                <label>Official School Name</label>
                                                <input type="text" name="school_name" class="form-control" value="<?= htmlspecialchars($school['school_name'] ?? '') ?>" placeholder="e.g. Amina Girls High School" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Complete Address</label>
                                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($school['address'] ?? '') ?>" placeholder="e.g. 21/MPR Gailywal, Lodhran">
                                            </div>
                                            <div class="form-group">
                                                <label>Contact Number</label>
                                                <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($school['contact'] ?? '') ?>" placeholder="e.g. 0300-1234567">
                                            </div>
                                            <div class="form-group">
                                                <label>Change Logo</label>
                                                <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewImg(this, 'logoCircle')">
                                            </div>
                                            <input type="hidden" name="old_logo" value="<?= $school['logo'] ?? '' ?>">
                                            <button type="submit" name="btn_update_school" class="btn btn-primary btn-lg btn-block shadow-sm">
                                                <i class="fas fa-save"></i> Save School Profile
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Right: Admin Profile -->
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header border-bottom">
                                        <h4><i class="fas fa-user-shield"></i> Admin Account</h4>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="card-body">
                                            <div class="preview-circle" id="adminCircle">
                                                <?php if (!empty($admin['profile_pic']) && file_exists('uploads/' . $admin['profile_pic'])): ?>
                                                    <img src="uploads/<?= $admin['profile_pic'] ?>">
                                                <?php else: ?>
                                                    <i class="fas fa-user-circle fa-3x text-muted"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="form-group">
                                                <label>Your Full Name</label>
                                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Email Address (Primary)</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" readonly style="background: #fdfdfd;">
                                            </div>
                                            <div class="form-group">
                                                <label>New Password</label>
                                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                                                <small class="text-muted">Password update will log you out automatically.</small>
                                            </div>
                                            <div class="form-group">
                                                <label>Update Profile Photo</label>
                                                <input type="file" name="profile_pic" class="form-control" accept="image/*" onchange="previewImg(this, 'adminCircle')">
                                            </div>
                                            <input type="hidden" name="old_pic" value="<?= $admin['profile_pic'] ?? '' ?>">
                                            <button type="submit" name="btn_update_admin" class="btn btn-dark btn-lg btn-block shadow-sm">
                                                <i class="fas fa-sync"></i> Update Account Settings
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <?php include 'include/footer.php'; ?>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="assets/js/app.min.js"></script>
    <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
    <script src="assets/js/scripts.js"></script>
    <script>
        // Image Preview Handler
        function previewImg(input, boxId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + boxId).html('<img src="' + e.target.result + '" style="width:100%; height:100%; object-fit:cover;">');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // SweetAlert Notifications
        <?php if ($swal): ?>
            swal({
                title: "<?= $swal['title'] ?>",
                text: "<?= $swal['text'] ?>",
                icon: "<?= $swal['type'] ?>",
                timer: 2500,
                buttons: false
            }).then(function() {
                <?php if ($redirect_to_logout): ?>
                    window.location.href = "logout.php";
                <?php else: ?>
                    window.location.href = "settings.php";
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>
</body>

</html>