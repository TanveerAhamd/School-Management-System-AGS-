<?php
// Current page ka filename nikalne ke liye
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
  /* Dropdown ke andar jo active link hai uska style */
  .sidebar-menu .dropdown-menu li.active a {
    color: #6777ef !important;
    /* Light Blue Text */
    background-color: #f0f3ff !important;
    /* Halki transparency wala background */
    font-weight: 600 !important;
  }

  /* Sub-links par hover effect */
  .sidebar-menu .dropdown-menu li a:hover {
    color: #6777ef !important;
  }

  /* Main dropdown jab active ho to sidebar ki left line ka color */
  .sidebar-menu li.active>a {
    border-left: 3px solid #6777ef !important;
    color: #6777ef !important;
  }

  /* Feather icons ka color jab wo section active ho */
  .sidebar-menu li.active>a i {
    color: #6777ef !important;
  }
</style>

<aside id="sidebar-wrapper">
  <div class="sidebar-brand">
    <a href="dashboard.php">
      <img alt="image" src="assets/img/AGHS Logo.png" class="header-logo" />
      <span class="logo-name">AGHS</span>
    </a>
  </div>
  <ul class="sidebar-menu">
    <li class="menu-header">Main</li>

    <li class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
      <a href="dashboard.php" class="nav-link"><i data-feather="monitor"></i><span>Dashboard</span></a>
    </li>

    <li class="menu-header">Management</li>

    <?php
    $student_pages = ['student-registration-form.php', 'student-list.php', 'promotion_detain.php', 'passout_drop.php', 'print_certificate.php', 'add-bulk-student.php'];
    $is_student_active = in_array($current_page, $student_pages);
    ?>
    <li class="dropdown <?= $is_student_active ? 'active' : '' ?>">
      <a href="#" class="menu-toggle nav-link has-dropdown"><i data-feather="users"></i><span>Students</span></a>
      <ul class="dropdown-menu" style="<?= $is_student_active ? 'display: block;' : '' ?>">
        <li class="<?= ($current_page == 'student-registration-form.php') ? 'active' : '' ?>"><a class="nav-link" href="student-registration-form.php">Registration Form</a></li>
        <li class="<?= ($current_page == 'student-list.php') ? 'active' : '' ?>"><a class="nav-link" href="student-list.php">List Students</a></li>
        <li class="<?= ($current_page == 'promotion_detain.php') ? 'active' : '' ?>"><a class="nav-link" href="promotion_detain.php">Promotion/Detian</a></li>
        <li class="<?= ($current_page == 'passout_drop.php') ? 'active' : '' ?>"><a class="nav-link" href="passout_drop.php">Passout/Drop</a></li>
        <li class="<?= ($current_page == 'print_certificate.php') ? 'active' : '' ?>"><a class="nav-link" href="print_certificate.php">Print Certificate</a></li>
        <li class="<?= ($current_page == 'add-bulk-student.php') ? 'active' : '' ?>"><a class="nav-link" href="add-bulk-student.php">Add Bulk Student</a></li>
      </ul>
    </li>

    <?php
    $class_pages = ['manage-class.php', 'manage-section.php', 'manage-session.php'];
    $is_class_active = in_array($current_page, $class_pages);
    ?>
    <li class="dropdown <?= $is_class_active ? 'active' : '' ?>">
      <a href="#" class="menu-toggle nav-link has-dropdown"><i data-feather="home"></i><span>Class Information</span></a>
      <ul class="dropdown-menu" style="<?= $is_class_active ? 'display: block;' : '' ?>">
        <li class="<?= ($current_page == 'manage-class.php') ? 'active' : '' ?>"><a class="nav-link" href="manage-class.php">Manage Class</a></li>
        <li class="<?= ($current_page == 'manage-section.php') ? 'active' : '' ?>"><a class="nav-link" href="manage-section.php">Manage Section</a></li>
        <li class="<?= ($current_page == 'manage-session.php') ? 'active' : '' ?>"><a class="nav-link" href="manage-session.php">Manage Session</a></li>
      </ul>
    </li>

    <?php
    $subject_pages = ['add-subject.php', 'subject-group.php'];
    $is_subject_active = in_array($current_page, $subject_pages);
    ?>
    <li class="dropdown <?= $is_subject_active ? 'active' : '' ?>">
      <a href="#" class="menu-toggle nav-link has-dropdown"><i data-feather="book-open"></i><span>Manage Subject</span></a>
      <ul class="dropdown-menu" style="<?= $is_subject_active ? 'display: block;' : '' ?>">
        <li class="<?= ($current_page == 'add-subject.php') ? 'active' : '' ?>"><a class="nav-link" href="add-subject.php">Add Subject</a></li>
        <li class="<?= ($current_page == 'subject-group.php') ? 'active' : '' ?>"><a class="nav-link" href="subject-group.php">Subject Group</a></li>
      </ul>
    </li>

    <?php
    $is_teacher_active = ($current_page == 'add-teacher.php');
    ?>
    <li class="dropdown <?= $is_teacher_active ? 'active' : '' ?>">
      <a href="#" class="menu-toggle nav-link has-dropdown"><i data-feather="user-check"></i><span>Manage Teacher</span></a>
      <ul class="dropdown-menu" style="<?= $is_teacher_active ? 'display: block;' : '' ?>">
        <li class="<?= ($current_page == 'add-teacher.php') ? 'active' : '' ?>"><a class="nav-link" href="add-teacher.php">Add Teacher</a></li>
      </ul>
    </li>

    <?php
    $transport_pages = ['transport.php', 'manage-route.php', 'manage-vehicle.php'];
    $is_transport_active = in_array($current_page, $transport_pages);
    ?>
    <li class="dropdown <?= $is_transport_active ? 'active' : '' ?>">
      <a href="#" class="menu-toggle nav-link has-dropdown"><i data-feather="truck"></i><span>Transportation</span></a>
      <ul class="dropdown-menu" style="<?= $is_transport_active ? 'display: block;' : '' ?>">
        <li class="<?= ($current_page == 'transport.php') ? 'active' : '' ?>"><a class="nav-link" href="transport.php">Pik_Drop Points</a></li>
        <li class="<?= ($current_page == 'manage-route.php') ? 'active' : '' ?>"><a class="nav-link" href="manage-route.php">Manage Route</a></li>
        <li class="<?= ($current_page == 'manage-vehicle.php') ? 'active' : '' ?>"><a class="nav-link" href="manage-vehicle.php">Manage Vehicle</a></li>
      </ul>
    </li>

    <?php
    $fee_pages = ['fee-type.php', 'pay-fee.php', 'manage-fee-record.php', 'discount-type.php'];
    $is_fee_active = in_array($current_page, $fee_pages);
    ?>
    <li class="dropdown <?= $is_fee_active ? 'active' : '' ?>">
      <a href="#" class="menu-toggle nav-link has-dropdown"><i data-feather="credit-card"></i><span>Fee Collection</span></a>
      <ul class="dropdown-menu" style="<?= $is_fee_active ? 'display: block;' : '' ?>">
        <li class="<?= ($current_page == 'fee-type.php') ? 'active' : '' ?>"><a class="nav-link" href="fee-type.php">Fee Type</a></li>
        <li class="<?= ($current_page == 'pay-fee.php') ? 'active' : '' ?>"><a class="nav-link" href="pay-fee.php">Pay Fee</a></li>
        <li class="<?= ($current_page == 'manage-fee-record.php') ? 'active' : '' ?>"><a class="nav-link" href="manage-fee-record.php">Manage Fee Record</a></li>
      </ul>
    </li>

    <?php
    $auth_pages = ['view_logs.php', 'register_admin.php'];
    $is_auth_active = in_array($current_page, $auth_pages);
    ?>
    <li class="dropdown <?= $is_auth_active ? 'active' : '' ?>">
      <a href="#" class="menu-toggle nav-link has-dropdown"><i data-feather="lock"></i><span>Auth</span></a>
      <ul class="dropdown-menu" style="<?= $is_auth_active ? 'display: block;' : '' ?>">
        <li class="<?= ($current_page == 'view_logs.php') ? 'active' : '' ?>"><a class="nav-link" href="view_logs.php">View Logs</a></li>
        <li class="<?= ($current_page == 'register_admin.php') ? 'active' : '' ?>"><a class="nav-link" href="register_admin.php">Register</a></li>
      </ul>
    </li>
  </ul>
</aside>