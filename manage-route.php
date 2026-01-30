<?php
require_once 'auth.php'; // Aapki database connection file

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 1. SAVE / UPDATE ROUTE LOGIC
// ==========================================
if (isset($_POST['btn_save_route'])) {
    $route_name = trim($_POST['route_name']);
    $vehicle_id = $_POST['vehicle_id'];
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $update_id = isset($_POST['update_id']) ? $_POST['update_id'] : null;

    if (!empty($route_name) && !empty($vehicle_id)) {
        try {
            if ($update_id) {
                $stmt = $pdo->prepare("UPDATE transport_routes SET route_name=?, vehicle_id=?, description=?, status=? WHERE id=?");
                $stmt->execute([$route_name, $vehicle_id, $description, $status, $update_id]);
                $_SESSION['swal_msg'] = ["title" => "Updated!", "text" => "Route updated successfully", "type" => "success"];
            } else {
                $stmt = $pdo->prepare("INSERT INTO transport_routes (route_name, vehicle_id, description, status) VALUES (?,?,?,?)");
                $stmt->execute([$route_name, $vehicle_id, $description, $status]);
                $_SESSION['swal_msg'] = ["title" => "Saved!", "text" => "New route added successfully", "type" => "success"];
            }
        } catch (Exception $e) {
            $_SESSION['swal_msg'] = ["title" => "Error!", "text" => $e->getMessage(), "type" => "error"];
        }
    }
    header("Location: manage-route.php");
    exit();
}

// ==========================================
// 2. DELETE ROUTE LOGIC
// ==========================================
if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    $pdo->prepare("DELETE FROM transport_routes WHERE id = ?")->execute([$del_id]);
    $_SESSION['swal_msg'] = ["title" => "Deleted!", "text" => "Route removed successfully", "type" => "success"];
    header("Location: manage-route.php");
    exit();
}

// Fetch all routes with vehicle details (JOIN Query)
$routes = $pdo->query("SELECT r.*, v.vehicle_no, v.vehicle_name FROM transport_routes r 
                       LEFT JOIN vehicles v ON r.vehicle_id = v.id 
                       ORDER BY r.id DESC")->fetchAll();

// Fetch all active vehicles for dropdown
$all_vehicles = $pdo->query("SELECT id, vehicle_name, vehicle_no FROM vehicles WHERE status = 1")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Manage Route| AGS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <?php include 'include/navbar.php'; ?>

      <div class="main-sidebar sidebar-style-2">
        <?php include 'include/asidebar.php'; ?>
      </div>
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="row bg-title">
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-body py-2 b-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">

                      <div class="mb-2 mb-md-0">
                        <h5 class="page-title mb-0">Transport Route</h5>
                      </div>

                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item">
                              <a href="#"><i class="fas fa-tachometer-alt"></i> Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                              <i class="fas fa-list"></i> Route
                            </li>
                          </ol>
                        </nav>
                      </div>

                    </div>

                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-4">
                <div class="card">

                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold">
                      <i class="fa fa-plus"></i>&nbsp;Add Route
                    </p>
                  </div>

                  <form method="POST" action="manage-route.php" class="needs-validation" novalidate>
                    <div class="card-body">

                      <div class="form-group">
                        <label>Route Name</label>
                        <input type="text" name="route_name" class="form-control" placeholder="e.g. North Route" required>
                        <div class="invalid-feedback">What's Route Title?</div>
                      </div>

                      <div class="form-group">
                        <label>Vehicle Number</label>
                        <select name="vehicle_id" class="form-control select2" required style="width:100%;">
                          <option value="">Select Vehicle</option>
                          <?php foreach($all_vehicles as $veh): ?>
                            <option value="<?= $veh['id'] ?>"><?= $veh['vehicle_no'] ?> (<?= $veh['vehicle_name'] ?>)</option>
                          <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">What's Vehicle Number?</div>
                      </div>

                      <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control select2" required style="width:100%;">
                          <option value="1">Active</option>
                          <option value="0">Inactive</option>
                        </select>
                        <div class="invalid-feedback">What's Status?</div>
                      </div>

                      <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" required>
                        <div class="invalid-feedback">What's Description?</div>
                      </div>
                    </div>
                    <div class="card-footer text-center">
                      <button type="submit" name="btn_save_route" class="btn btn-info btn-block btn-rounded btn-sm">
                        <i class="fa fa-plus"></i>&nbsp;Save Route
                      </button>
                    </div>
                  </form>

                </div>
              </div>

              <div class="col-sm-8">
                <div class="card">

                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold transform-uppercase">
                      <i class="fa fa-list"></i>&nbsp;List Routes
                    </p>
                  </div>

                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Route Name</th>
                            <th>Vehicle No</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Options</th>
                          </tr>
                        </thead>

                        <tbody>
                          <?php foreach($routes as $key => $row): ?>
                          <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= $row['route_name'] ?></td>
                            <td><?= $row['vehicle_no'] ?></td>
                            <td><?= $row['description'] ?></td>
                            <td>
                              <?php if($row['status'] == 1): ?>
                                <span class="badge badge-success">Active</span>
                              <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <button class="btn btn-sm btn-primary btn-edit" 
                                data-id="<?= $row['id'] ?>"
                                data-name="<?= $row['route_name'] ?>"
                                data-veh="<?= $row['vehicle_id'] ?>"
                                data-desc="<?= $row['description'] ?>"
                                data-status="<?= $row['status'] ?>">
                                <i class="fa fa-edit"></i>
                              </button>
                              <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $row['id'] ?>">
                                <i class="fa fa-trash"></i>
                              </button>
                            </td>
                          </tr>
                          <?php endforeach; ?>
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
      <?php include 'include/footer.php'; ?>

      <div class="modal fade" id="editRouteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <form method="POST" action="manage-route.php" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Route</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="update_id" id="edit_id">
                <div class="form-group">
                  <label>Route Name</label>
                  <input type="text" name="route_name" id="edit_name" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Vehicle</label>
                  <select name="vehicle_id" id="edit_veh" class="form-control" required>
                    <option value="">Select Vehicle</option>
                    <?php foreach($all_vehicles as $veh): ?>
                      <option value="<?= $veh['id'] ?>"><?= $veh['vehicle_no'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label>Description</label>
                  <input type="text" name="description" id="edit_desc" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Status</label>
                  <select name="status" id="edit_status" class="form-control" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                  </select>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
              <button type="submit" name="btn_save_route" class="btn btn-info btn-sm">
                <i class="fa fa-save"></i>&nbsp;Update
              </button>
            </div>
          </form>
        </div>
      </div>
      </div>
  </div>
  <script src="assets/js/app.min.js"></script>
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  <script src="assets/js/page/datatables.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    $(document).ready(function() {
      // Show alerts
      <?php if(isset($_SESSION['swal_msg'])): ?>
        swal("<?= $_SESSION['swal_msg']['title'] ?>", "<?= $_SESSION['swal_msg']['text'] ?>", "<?= $_SESSION['swal_msg']['type'] ?>");
        <?php unset($_SESSION['swal_msg']); ?>
      <?php endif; ?>

      // Edit Logic
      $('.btn-edit').on('click', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_veh').val($(this).data('veh'));
        $('#edit_desc').val($(this).data('desc'));
        $('#edit_status').val($(this).data('status'));
        $('#editRouteModal').modal('show');
      });

      // Delete Logic
      $('.btn-delete').on('click', function() {
        var id = $(this).data('id');
        swal({
          title: "Are you sure?",
          text: "Once deleted, you will not be able to recover this route!",
          icon: "warning",
          buttons: true,
          dangerMode: true,
        }).then((willDelete) => {
          if (willDelete) {
            window.location.href = "manage-route.php?del=" + id;
          }
        });
      });
    });
  </script>
</body>
</html>