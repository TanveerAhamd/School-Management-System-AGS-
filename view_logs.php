<?php
require_once 'auth.php'; // Security check aur DB connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Audit Logs - AGS System</title>
    <link rel="stylesheet" href="assets/css/app.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/components.css">
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
            <div class="main-content">
                <section class="section">
                    <div class="section-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>System Audit Logs (Admin Activities)</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="table-1">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Admin Name</th>
                                                        <th>Action</th>
                                                        <th>Page URL</th>
                                                        <th>IP Address</th>
                                                        <th>Date & Time</th>
                                                        <th>Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Database se logs fetch karna (Admins table ke saath join karke)
                                                    $sql = "SELECT al.*, a.full_name 
                                  FROM audit_logs al 
                                  LEFT JOIN admins a ON al.admin_id = a.id 
                                  ORDER BY al.created_at DESC";
                                                    $stmt = $pdo->query($sql);
                                                    while ($row = $stmt->fetch()) {
                                                        // Action ke mutabiq Badge ka color set karna
                                                        $badge_class = 'badge-info';
                                                        if ($row['action_type'] == 'LOGIN') $badge_class = 'badge-success';
                                                        if ($row['action_type'] == 'LOGOUT') $badge_class = 'badge-danger';
                                                        if ($row['action_type'] == 'TIMEOUT') $badge_class = 'badge-warning';
                                                    ?>
                                                        <tr>
                                                            <td><?php echo $row['id']; ?></td>
                                                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                                            <td>
                                                                <div class="badge <?php echo $badge_class; ?>">
                                                                    <?php echo $row['action_type']; ?>
                                                                </div>
                                                            </td>
                                                            <td><small><?php echo $row['page_url']; ?></small></td>
                                                            <td><?php echo $row['ip_address']; ?></td>
                                                            <td><?php echo date('d-M-Y H:i A', strtotime($row['created_at'])); ?></td>
                                                            <td><?php echo htmlspecialchars($row['action_details']); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
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

    <script src="assets/js/app.min.js"></script>
    <script src="assets/bundles/datatables/datatables.min.js"></script>
    <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/scripts.js"></script>

    <script>
        $(document).ready(function() {
            $('#table-1').DataTable({
                "order": [
                    [0, "desc"]
                ] // Newest logs pehle dikhayega
            });
        });
    </script>
</body>

</html>