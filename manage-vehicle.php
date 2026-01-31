<?php
require_once 'auth.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 1. SAVE / UPDATE VEHICLE LOGIC
// ==========================================
if (isset($_POST['btn_save'])) {
    $vehicle_name = trim($_POST['vehicle_name']);
    $vehicle_no   = trim($_POST['vehicle_no']);
    $vehicle_model = trim($_POST['vehicle_model']);
    $driver_name  = trim($_POST['driver_name']);
    $driver_contact = trim($_POST['driver_contact']);
    $description  = trim($_POST['description']);
    $status       = $_POST['status'];
    $update_id    = isset($_POST['update_id']) ? $_POST['update_id'] : null;

    if (!empty($vehicle_name) && !empty($vehicle_no)) {
        try {
            if ($update_id) {
                // Update Query
                $stmt = $pdo->prepare("UPDATE vehicles SET vehicle_name=?, vehicle_no=?, vehicle_model=?, driver_name=?, driver_contact=?, description=?, status=? WHERE id=?");
                $stmt->execute([$vehicle_name, $vehicle_no, $vehicle_model, $driver_name, $driver_contact, $description, $status, $update_id]);
                $_SESSION['swal_msg'] = ["title" => "Updated!", "text" => "Vehicle details updated successfully", "type" => "success"];
            } else {
                // Insert Query
                $stmt = $pdo->prepare("INSERT INTO vehicles (vehicle_name, vehicle_no, vehicle_model, driver_name, driver_contact, description, status) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$vehicle_name, $vehicle_no, $vehicle_model, $driver_name, $driver_contact, $description, $status]);
                $_SESSION['swal_msg'] = ["title" => "Saved!", "text" => "New vehicle added successfully", "type" => "success"];
            }
        } catch (Exception $e) {
            $_SESSION['swal_msg'] = ["title" => "Error!", "text" => $e->getMessage(), "type" => "error"];
        }
    } else {
        $_SESSION['swal_msg'] = ["title" => "Warning!", "text" => "Please fill mandatory fields", "type" => "warning"];
    }
    header("Location: manage-vehicle.php");
    exit();
}

// ==========================================
// 2. DELETE VEHICLE LOGIC
// ==========================================
if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    $pdo->prepare("DELETE FROM vehicles WHERE id = ?")->execute([$del_id]);
    $_SESSION['swal_msg'] = ["title" => "Deleted!", "text" => "Vehicle removed from system", "type" => "success"];
    header("Location: manage-vehicle.php");
    exit();
}

// Fetch all vehicles
$vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Manage Vehicle| AGHS Lodhran</title>
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/custom.css">
  <link rel="icon" href="assets/img/favicon.png">
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
                        <h5 class="page-title mb-0">Manage Vehicle</h5>
                      </div>
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="#"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page"> Vehicle</li>
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
                    <p class="mb-0 font-weight-bold"><i class="fa fa-plus"></i>&nbsp;Add Vehicle</p>
                  </div>
                  <form method="POST" action="manage-vehicle.php" class="needs-validation" novalidate="">
                    <div class="card-body">
                      <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="vehicle_name" class="form-control" placeholder="e.g. School Van" required>
                      </div>
                      <div class="form-group">
                        <label>Vehicle Number</label>
                        <input type="text" name="vehicle_no" class="form-control" placeholder="e.g. LEA-1234" required>
                      </div>
                      <div class="form-group">
                        <label>Vehicle Model</label>
                        <input type="text" name="vehicle_model" class="form-control" placeholder="e.g. 2022">
                      </div>
                      <div class="form-group">
                        <label>Driver Name</label>
                        <input type="text" name="driver_name" class="form-control">
                      </div>
                      <div class="form-group">
                        <label>Driver Contact</label>
                        <input type="text" name="driver_contact" class="form-control">
                      </div>
                      <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control">
                      </div>
                      <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control select2" required>
                          <option value="1">Active</option>
                          <option value="0">Inactive</option>
                        </select>
                      </div>
                    </div>
                    <div class="card-footer text-center">
                      <button type="submit" name="btn_save" class="btn btn-info btn-block btn-rounded">
                        <i class="fa fa-plus"></i>&nbsp;Save Vehicle
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <div class="col-sm-8">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold transform-uppercase"><i class="fa fa-list"></i>&nbsp;List Vehicle</p>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Vehicle Name</th>
                            <th>Vehicle No</th>
                            <th>Model</th>
                            <th>Driver</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Options</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($vehicles as $key => $row): ?>
                          <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= $row['vehicle_name'] ?></td>
                            <td><?= $row['vehicle_no'] ?></td>
                            <td><?= $row['vehicle_model'] ?></td>
                            <td><?= $row['driver_name'] ?></td>
                            <td><?= $row['driver_contact'] ?></td>
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
                                data-name="<?= $row['vehicle_name'] ?>"
                                data-no="<?= $row['vehicle_no'] ?>"
                                data-model="<?= $row['vehicle_model'] ?>"
                                data-driver="<?= $row['driver_name'] ?>"
                                data-contact="<?= $row['driver_contact'] ?>"
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

      <div class="modal fade" id="editVehicleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <form method="POST" action="manage-vehicle.php" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-edit"></i>&nbsp;Edit Vehicle</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="update_id" id="edit_id">
              <div class="form-group">
                <label>Name</label>
                <input type="text" name="vehicle_name" id="edit_name" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Vehicle Number</label>
                <input type="text" name="vehicle_no" id="edit_no" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Vehicle Model</label>
                <input type="text" name="vehicle_model" id="edit_model" class="form-control">
              </div>
              <div class="form-group">
                <label>Driver Name</label>
                <input type="text" name="driver_name" id="edit_driver" class="form-control">
              </div>
              <div class="form-group">
                <label>Driver Contact</label>
                <input type="text" name="driver_contact" id="edit_contact" class="form-control">
              </div>
              <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" id="edit_desc" class="form-control">
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
              <button type="submit" name="btn_save" class="btn btn-info btn-sm">Update Vehicle</button>
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

  <script>
    $(document).ready(function() {
      // 1. Show SweetAlert Messages
      <?php if(isset($_SESSION['swal_msg'])): ?>
        swal("<?= $_SESSION['swal_msg']['title'] ?>", "<?= $_SESSION['swal_msg']['text'] ?>", "<?= $_SESSION['swal_msg']['type'] ?>");
        <?php unset($_SESSION['swal_msg']); ?>
      <?php endif; ?>

      // 2. Edit Modal Logic
      $('.btn-edit').on('click', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_no').val($(this).data('no'));
        $('#edit_model').val($(this).data('model'));
        $('#edit_driver').val($(this).data('driver'));
        $('#edit_contact').val($(this).data('contact'));
        $('#edit_desc').val($(this).data('desc'));
        $('#edit_status').val($(this).data('status'));
        $('#editVehicleModal').modal('show');
      });

      // 3. Delete Confirmation SweetAlert
      $('.btn-delete').on('click', function() {
        var id = $(this).data('id');
        swal({
          title: "Are you sure?",
          text: "Vehicle record will be deleted permanently!",
          icon: "warning",
          buttons: true,
          dangerMode: true,
        }).then((willDelete) => {
          if (willDelete) {
            window.location.href = "manage-vehicle.php?del=" + id;
          }
        });
      });
    });
  </script>
</body>
</html>