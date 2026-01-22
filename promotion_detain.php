<?php
require_once 'auth.php';

/**
 * 1. BACKEND LOGIC
 */
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] == 'fetch_sections') {
        $stmt = $pdo->prepare("SELECT id, section_name FROM sections WHERE class_id = ?");
        $stmt->execute([$_GET['class_id'] ?? 0]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($_GET['action'] == 'fetch_students') {
        $sess = $_GET['curr_session'] ?? '';
        $cls  = $_GET['curr_class'] ?? '';
        $sec  = $_GET['curr_section'] ?? '';

        $query = "SELECT s.id, s.reg_no, s.student_name, s.student_photo, 
                         s.is_promoted, s.is_detained, s.is_passout, s.is_dropout,
                         c.class_name, sec.section_name 
                  FROM students s
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN sections sec ON s.section_id = sec.id
                  WHERE s.is_deleted = 0";

        $params = [];
        if (!empty($sess)) {
            $query .= " AND s.session = ?";
            $params[] = $sess;
        }
        if (!empty($cls)) {
            $query .= " AND s.class_id = ?";
            $params[] = $cls;
        }
        if (!empty($sec)) {
            $query .= " AND s.section_id = ?";
            $params[] = $sec;
        }

        $query .= " ORDER BY s.id DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($_GET['action'] == 'process_action' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $ids = $_POST['student_ids'];
            $type = $_POST['type'];
            $t_sess = $_POST['t_session'];
            $t_cls  = $_POST['t_class'];
            $t_sec  = $_POST['t_section'];

            if (empty($ids)) throw new Exception("Please select students.");

            $is_p = ($type === 'Promote') ? 1 : 0;
            $is_d = ($type === 'Detain') ? 1 : 0;

            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $sql = "UPDATE students SET session = ?, class_id = ?, section_id = ?, is_promoted = ?, is_detained = ? WHERE id IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge([$t_sess, $t_cls, $t_sec, $is_p, $is_d], $ids));

            echo json_encode(['status' => 'success', 'message' => "$type processed for " . count($ids) . " students."]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}

$classes = $pdo->query("SELECT * FROM classes")->fetchAll();
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Promotion & Detain | AGS</title>
    <link rel="stylesheet" href="assets/css/app.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <style>
        #promotion_module .st-photo {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #eee;
        }

        #promotion_module .badge-status {
            font-size: 10px;
            padding: 4px 8px;
            text-transform: uppercase;
        }

        .dt-buttons {
            margin-bottom: 15px;
        }

        /* Export buttons spacing */
    </style>
</head>

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <?php include 'include/navbar.php'; ?>
            <div class="main-sidebar sidebar-style-2"><?php include 'include/asidebar.php'; ?></div>

            <div class="main-content" id="promotion_module">
                <section class="section">
                    <div class="section-body">
                        <div class="card">
                            <div class="card-body ">
                                <div class="row">
                                    <div class="col-md-2 form-group">
                                        <label>Session</label>
                                        <select id="f_sess" class="form-control select2">
                                            <option value="">All Sessions</option>
                                            <?php foreach ($sessions as $s) echo "<option value='{$s['id']}'>{$s['session_name']}</option>"; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label>Class</label>
                                        <select id="f_cls" class="form-control select2">
                                            <option value="">All Classes</option>
                                            <?php foreach ($classes as $c) echo "<option value='{$c['id']}'>{$c['class_name']}</option>"; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label>Section</label>
                                        <select id="f_sec" class="form-control select2">
                                            <option value="">All Sections</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 text-right mt-4">
                                        <button id="btnFetch" class="btn btn-primary"><i class="fa fa-search"></i> Fetch Students</button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body border-top py-3" id="action_bar" style="display:none; background: #f4faff;">
                                <div class="row align-items-end">
                                    <div class="col-md-2"><label>Target Session</label>
                                        <select id="t_sess" class="form-control"><?php foreach ($sessions as $s) echo "<option value='{$s['id']}'>{$s['session_name']}</option>"; ?></select>
                                    </div>
                                    <div class="col-md-2"><label>Target Class</label>
                                        <select id="t_cls" class="form-control">
                                            <option value="">Select Class</option><?php foreach ($classes as $c) echo "<option value='{$c['id']}'>{$c['class_name']}</option>"; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2"><label>Target Section</label>
                                        <select id="t_sec" class="form-control">
                                            <option value="">Select Section</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button class="btn btn-success btn-action" data-type="Promote">Bulk Promote</button>
                                        <button class="btn btn-warning btn-action" data-type="Detain">Mark Detain</button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="mainPromoTable" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="checkAllMaster"></th>
                                                <th>Reg#</th>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Current Class</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="promo_tbody">
                                            <tr>
                                                <td colspan="6" class="text-center">Search to load students.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <?php include 'include/footer.php'; ?>
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
    <script src="assets/bundles/datatables/export-tables/buttons.html5.min.js"></script>

    <script src="./assets/js/sweetalert2.js"></script>
    <script src="assets/js/scripts.js"></script>

    <script>
        $(document).ready(function() {
            $('.loader').fadeOut('slow');

            const loadSec = (c, s) => {
                $(c).change(function() {
                    $.getJSON('promotion_detain.php?action=fetch_sections&class_id=' + $(this).val(), function(d) {
                        let h = '<option value="">All Sections</option>';
                        d.forEach(x => h += `<option value="${x.id}">${x.section_name}</option>`);
                        $(s).html(h);
                    });
                });
            };
            loadSec('#f_cls', '#f_sec');
            loadSec('#t_cls', '#t_sec');

            $('#btnFetch').click(function() {
                let p = {
                    curr_session: $('#f_sess').val(),
                    curr_class: $('#f_cls').val(),
                    curr_section: $('#f_sec').val()
                };
                $.getJSON('promotion_detain.php?action=fetch_students', p, function(res) {
                    if ($.fn.DataTable.isDataTable('#mainPromoTable')) {
                        $('#mainPromoTable').DataTable().destroy();
                    }

                    let h = '';
                    res.forEach(s => {
                        let img = s.student_photo ? s.student_photo : 'assets/img/userdummypic.png';
                        let status = 'Active',
                            bCls = 'badge-primary';
                        if (s.is_promoted == 1) {
                            status = 'Promoted';
                            bCls = 'badge-success';
                        } else if (s.is_detained == 1) {
                            status = 'Detained';
                            bCls = 'badge-warning';
                        }

                        h += `<tr>
                                <td><input type="checkbox" class="st-cb" value="${s.id}"></td>
                                <td>${s.reg_no}</td>
                                <td><img src="${img}" class="st-photo"></td>
                                <td>${s.student_name}</td>
                                <td>${s.class_name || 'N/A'}</td>
                                <td><span class="badge ${bCls} badge-status">${status}</span></td>
                              </tr>`;
                    });

                    $('#promo_tbody').html(h || '<tr><td colspan="6" class="text-center">No data.</td></tr>');

                    // Initialize DataTable with Export Buttons
                    $('#mainPromoTable').DataTable({
                        dom: 'Bfrtip',
                        buttons: [{
                                extend: 'copy',
                                className: 'btn btn-secondary'
                            },
                            {
                                extend: 'excel',
                                className: 'btn btn-success',
                                title: 'Student_Promotion_List'
                            },
                            {
                                extend: 'pdf',
                                className: 'btn btn-danger',
                                title: 'Student_Promotion_List'
                            },
                            {
                                extend: 'print',
                                className: 'btn btn-info'
                            }
                        ]
                    });
                    $('#action_bar').fadeIn();
                });
            });

            $('#checkAllMaster').click(function() {
                $('.st-cb').prop('checked', this.checked);
            });

            $('.btn-action').click(function() {
                let type = $(this).data('type'),
                    ids = [];
                $('.st-cb:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length === 0) return Swal.fire('Error', 'Select students.', 'error');

                $.post('promotion_detain.php?action=process_action', {
                    student_ids: ids,
                    type: type,
                    t_session: $('#t_sess').val(),
                    t_class: $('#t_cls').val(),
                    t_section: $('#t_sec').val()
                }, function(resp) {
                    Swal.fire('Success', resp.message, 'success').then(() => $('#btnFetch').click());
                });
            });
        });
    </script>
</body>

</html>