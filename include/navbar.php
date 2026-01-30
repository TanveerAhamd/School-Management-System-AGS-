<?php
// 1. ڈیٹا بیس سے ایڈمن کی تازہ ترین معلومات نکالنا
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $st_admin = $pdo->prepare("SELECT full_name, profile_pic FROM admins WHERE id = ?");
    $st_admin->execute([$admin_id]);
    $admin_data = $st_admin->fetch(PDO::FETCH_ASSOC);

    // نام اور تصویر سیٹ کرنا
    $display_name = $admin_data['full_name'] ?? 'Admin';
    $display_pic = (!empty($admin_data['profile_pic']) && file_exists('uploads/' . $admin_data['profile_pic'])) 
                   ? 'uploads/' . $admin_data['profile_pic'] 
                   : 'assets/img/user.png';
} else {
    $display_name = "Admin";
    $display_pic = "assets/img/user.png";
}
?>

<nav class="navbar navbar-expand-lg main-navbar sticky">
    <div class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn"> <i data-feather="align-justify"></i></a></li>
            <li class="d-none d-md-block"><a href="#" class="nav-link nav-link-lg fullscreen-btn">
                <i data-feather="maximize"></i>
            </a></li>
            <li> <!-- filter session start -->
                <?php
                // Current page detection
                $current_page = basename($_SERVER['PHP_SELF']);
                ?>

                <!-- Filter Session Section -->
                <?php if ($current_page == 'dashboard.php'): ?>
                    <!-- Yeh sirf dashboard.php par nazar aaye ga -->
                    <div class="card-footer bg-white py-2 d-flex justify-content-end" style="border-radius: 0 0 15px 15px;">
                        <form method="GET" class="form-inline">
                            <span class=" d-none d-md-block mr-2 font-weight-bold small text-dark">Filter Session:</span>
                            <select name="session_id" class="form-control form-control-sm select2" onchange="this.form.submit()">
                                <option value="all" <?= (isset($f_sess) && $f_sess == 'all' ? 'selected' : '') ?>>All Sessions</option>
                                <?php
                                if (isset($sessions)) {
                                    foreach ($sessions as $s) {
                                        echo "<option value='{$s['id']}' " . (isset($f_sess) && $f_sess == $s['id'] ? 'selected' : '') . ">{$s['session_name']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Baki pages par ya toh khali rkhden ya dropdown ko disable krden -->
                    <div class="align-items-baseline bg-white card-footer d-flex justify-content-end py-2" style="border-radius: 0 0 15px 15px; opacity: 0.6;">
                        <span class="d-none d-md-block mr-2 font-weight-bold small text-muted">Filter Session:</span>
                        <select class="form-control form-control-sm" disabled>
                            <option>Not Available</option>
                        </select>
                    </div>
                <?php endif; ?>
            </li>
        </ul>
    </div>

    <!-- filter session close -->
    <ul class="navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user"> 
                <!-- ایڈمن کی تصویر یہاں نظر آئے گی -->
                <img alt="image" src="<?= $display_pic ?>" class="user-img-radious-style" style="width:30px; height:30px; object-fit:cover;"> 
                <span class="d-sm-none d-lg-inline-block"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right pullDown">
                <!-- ایڈمن کا نام یہاں نظر آئے گا -->
                <div class="dropdown-title">Hello, <?= htmlspecialchars($display_name) ?></div>

                <!-- سیٹنگز کا لنک -->
                <a href="settings.php" class="dropdown-item has-icon"> 
                    <i class="far fa-user"></i> Profile Settings
                </a>

                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item has-icon text-danger"> <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>