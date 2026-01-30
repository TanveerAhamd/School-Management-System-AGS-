<?php
require_once 'auth.php'; // Aapki database connection file

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 1. SAVE / UPDATE TRANSPORT LOGIC
// ==========================================
if (isset($_POST['btn_save_transport'])) {
    $name = trim($_POST['name']);
    $route_id = $_POST['route_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $fare = $_POST['fare'];
    $description = trim($_POST['description']);
    $update_id = isset($_POST['update_id']) ? $_POST['update_id'] : null;

    if (!empty($name) && !empty($route_id) && !empty($vehicle_id)) {
        try {
            if ($update_id) {
                // Update existing record
                $stmt = $pdo->prepare("UPDATE transport_allocations SET transport_name=?, route_id=?, vehicle_id=?, fare=?, description=? WHERE id=?");
                $stmt->execute([$name, $route_id, $vehicle_id, $fare, $description, $update_id]);
                $_SESSION['swal_msg'] = ["title" => "Updated!", "text" => "Transport record updated successfully", "type" => "success"];
            } else {
                // Insert new record
                $stmt = $pdo->prepare("INSERT INTO transport_allocations (transport_name, route_id, vehicle_id, fare, description) VALUES (?,?,?,?,?)");
                $stmt->execute([$name, $route_id, $vehicle_id, $fare, $description]);
                $_SESSION['swal_msg'] = ["title" => "Saved!", "text" => "New transport added successfully", "type" => "success"];
            }
        } catch (Exception $e) {
            $_SESSION['swal_msg'] = ["title" => "Error!", "text" => $e->getMessage(), "type" => "error"];
        }
    }
    header("Location: transport.php");
    exit();
}

// ==========================================
// 2. DELETE TRANSPORT LOGIC
// ==========================================
if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    $pdo->prepare("DELETE FROM transport_allocations WHERE id = ?")->execute([$del_id]);
    $_SESSION['swal_msg'] = ["title" => "Deleted!", "text" => "Record removed successfully", "type" => "success"];
    header("Location: transport.php");
    exit();
}

// ==========================================
// 3. FETCH ACTIVE DATA FOR DROPDOWNS
// ==========================================
// Sirf woh Routes aur Vehicles uthayen jinka status Active (1) hai
$active_routes = $pdo->query("SELECT id, route_name FROM transport_routes WHERE status = 1")->fetchAll();
$active_vehicles = $pdo->query("SELECT id, vehicle_no, vehicle_name FROM vehicles WHERE status = 1")->fetchAll();

// Main Table List with JOIN to get Route Name and Vehicle Number
$list = $pdo->query("SELECT t.*, r.route_name, v.vehicle_no 
                     FROM transport_allocations t
                     LEFT JOIN transport_routes r ON t.route_id = r.id
                     LEFT JOIN vehicles v ON t.vehicle_id = v.id
                     ORDER BY t.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Transport - School Management System</title>
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
                        <h5 class="page-title mb-0">Transport Management</h5>
                      </div>
                      <div>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="#"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                            <li class="breadcrumb-item active"><i class="fas fa-bus"></i> Transport</li>
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
                    <p class="mb-0 font-weight-bold"><i class="fa fa-plus"></i>&nbsp;Add Transport Allocation</p>
                  </div>
                  <form method="POST" action="transport.php" class="needs-validation" novalidate>
                    <div class="card-body">
                      <div class="form-group">
                        <label>Name / Title</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Morning Shift A" required>
                        <div class="invalid-feedback">Please enter a name</div>
                      </div>

                      <div class="form-group">
                        <label>Transport Route (Active Only)</label>
                        <select name="route_id" class="form-control select2" required style="width:100%;">
                          <option value="">Select Route</option>
                          <?php foreach($active_routes as $route): ?>
                            <option value="<?= $route['id'] ?>"><?= $route['route_name'] ?></option>
                          <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a route</div>
                      </div>

                      <div class="form-group">
                        <label>Vehicle (Active Only)</label>
                        <select name="vehicle_id" class="form-control select2" required style="width:100%;">
                          <option value="">Select Vehicle</option>
                          <?php foreach($active_vehicles as $veh): ?>
                            <option value="<?= $veh['id'] ?>"><?= $veh['vehicle_no'] ?> (<?= $veh['vehicle_name'] ?>)</option>
                          <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a vehicle</div>
                      </div>

                      <div class="form-group">
                        <label>Route Fare (Monthly)</label>
                        <input type="number" name="fare" class="form-control" placeholder="0.00" required>
                        <div class="invalid-feedback">What's the monthly fare?</div>
                      </div>

                      <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                      </div>
                    </div>
                    <div class="card-footer text-center">
                      <button type="submit" name="btn_save_transport" class="btn btn-info btn-block btn-rounded btn-sm">
                        <i class="fa fa-save"></i>&nbsp;Save Transport
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <div class="col-sm-8">
                <div class="card">
                  <div class="card-header border-bottom">
                    <p class="mb-0 font-weight-bold transform-uppercase"><i class="fa fa-list"></i>&nbsp;List Transport Allocations</p>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Route</th>
                            <th>Vehicle No</th>
                            <th>Fare</th>
                            <th>Options</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach($list as $key => $row): ?>
                          <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= $row['transport_name'] ?></td>
                            <td><?= $row['route_name'] ?></td>
                            <td><?= $row['vehicle_no'] ?></td>
                            <td><?= number_format($row['fare'], 2) ?></td>
                            <td>
                              <button class="btn btn-sm btn-primary btn-edit" 
                                data-id="<?= $row['id'] ?>"
                                data-name="<?= $row['transport_name'] ?>"
                                data-route="<?= $row['route_id'] ?>"
                                data-veh="<?= $row['vehicle_id'] ?>"
                                data-fare="<?= $row['fare'] ?>"
                                data-desc="<?= $row['description'] ?>">
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

      <div class="modal fade" id="editTransportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <form method="POST" action="transport.php" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Transport Details</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="update_id" id="edit_id">
                <div class="form-group">
                  <label>Name</label>
                  <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Route</label>
                  <select name="route_id" id="edit_route" class="form-control select2" required style="width:100%;">
                    <?php foreach($active_routes as $route): ?>
                      <option value="<?= $route['id'] ?>"><?= $route['route_name'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Vehicle</label>
                  <select name="vehicle_id" id="edit_veh" class="form-control select2" required style="width:100%;">
                    <?php foreach($active_vehicles as $veh): ?>
                      <option value="<?= $veh['id'] ?>"><?= $veh['vehicle_no'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Route Fare</label>
                  <input type="number" name="fare" id="edit_fare" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Description</label>
                  <textarea name="description" id="edit_desc" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
              <button type="submit" name="btn_save_transport" class="btn btn-info btn-sm"><i class="fa fa-check"></i> Update Changes</button>
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
  <script src="assets/bundles/datatables/export-tables/buttons.flash.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>
  
  <script src="assets/bundles/sweetalert/sweetalert.min.js"></script>
  
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    $(document).ready(function() {
      // Initialize DataTable with Export Buttons
      if ($('#tableExport').length) {
        $('#tableExport').DataTable({
          dom: 'Bfrtip',
          buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
          ]
        });
      }

      // Show SweetAlert Messages
      <?php if(isset($_SESSION['swal_msg'])): ?>
        swal("<?= $_SESSION['swal_msg']['title'] ?>", "<?= $_SESSION['swal_msg']['text'] ?>", "<?= $_SESSION['swal_msg']['type'] ?>");
        <?php unset($_SESSION['swal_msg']); ?>
      <?php endif; ?>

      // Open Edit Modal and Fill Data
      $('.btn-edit').on('click', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_route').val($(this).data('route')).trigger('change');
        $('#edit_veh').val($(this).data('veh')).trigger('change');
        $('#edit_fare').val($(this).data('fare'));
        $('#edit_desc').val($(this).data('desc'));
        $('#editTransportModal').modal('show');
      });

      // Professional Delete Confirmation
      $('.btn-delete').on('click', function() {
        var id = $(this).data('id');
        swal({
          title: "Are you sure?",
          text: "This transport allocation will be deleted permanently!",
          icon: "warning",
          buttons: true,
          dangerMode: true,
        }).then((willDelete) => {
          if (willDelete) {
            window.location.href = "transport.php?del=" + id;
          }
        });
      });
    });
  </script>
</body>
</html>