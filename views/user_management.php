<?php
require_once "../models/user_model.php";
include_once "../controllers/user_controller.php";
require_once '../config/database_conn.php';
include_once "../includes/status_helper.php";  // If needed for badges, else remove

$conn = $databaseconn;

$stats = getUserStats($conn);
$users = getAllUsers($conn);
$roles = ['Admin', 'Staff', 'Driver'];  // For filters/modals
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CRISNIL TRADING CORPORATION</title>
    <link rel="stylesheet" href="../styles/logistics.css">
    <link rel="stylesheet" href="../styles/user_management.css">
    <link rel="stylesheet" href="../styles/dashboard2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>

    <main class="main-content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="page-title">User Management</h1>
                <div>
                    <button class="export-btn btn btn-primary">
                        <i class="fa fa-download"></i> Export
                    </button>
                </div>
            </div>

            <!-- User Stats Cards -->
            <div class="row mb-4 dashboard-cards">
                <div class="col-md-4">
                    <div class="card blue">
                        <h3>Admins</h3>
                        <p style="color:white;">Total: <?= $stats['admins'] ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card green">
                        <h3>Staff</h3>
                        <p style="color:white;">Total: <?= $stats['staff'] ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card red">
                        <h3>Drivers</h3>
                        <p style="color:white;">Total: <?= $stats['drivers'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="search-wrapper mb-4">
                <input type="text" id="searchInput" class="search-input" placeholder="Search by ID, Name, or Role...">
                <i class="fa fa-search search-icon"></i>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table class="orders-table" id="usersTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" class="row-check"></td>
                            <td><strong>#<?= $row['USER_ID'] ?></strong></td>
                            <td><?= htmlspecialchars($row['FIRST_NAME']) ?></td>
                            <td><?= htmlspecialchars($row['LAST_NAME']) ?></td>
                            <td><?= htmlspecialchars($row['EMAIL_ADDRESS']) ?></td>
                            <td><?= htmlspecialchars($row['PHONE_NUM']) ?></td>
                            <td><?= htmlspecialchars($row['USER_ROLE']) ?></td>
                            <td><?= renderStatusBadge($row['USER_STATUS']) ?></td>
                            <td>
                                <i class="fa fa-pencil edit-icon" data-id="<?= $row['USER_ID'] ?>" title="Edit"></i>
                                <i class="fa fa-archive archive-icon" data-id="<?= $row['USER_ID'] ?>" title="Archive"></i>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Floating + Button -->
            <button class="floating-add-btn" data-bs-toggle="modal" data-bs-target="#registerUserModal">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    </main>

    <!-- Register User Modal -->
    <div class="modal fade" id="registerUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Register New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../controllers/user_controller.php?action=create" method="POST" id="registerForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" required pattern="\d{10,15}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required pattern="^[a-zA-Z0-9_-]{4,20}$" title="4-20 chars, alphanumeric, _ or - only, no spaces">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$" title="Min 8 chars, upper, lower, number, symbol">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="">Select Role</option>
                                    <option value="Administrator">Admin</option>
                                    <option value="Staff">Staff</option>
                                    <option value="Rider">Driver</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Register User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User #<span id="editUserIdDisplay"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="../controllers/user_controller.php?action=update" method="POST" id="editForm">
                    <input type="hidden" name="user_id" id="editUserId">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" id="editFirstName" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="editLastName" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" id="editPhone" class="form-control" required pattern="[0-9+ -]{10,15}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" id="editUsername" class="form-control" readonly> <!-- Not editable -->
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="editRole" class="form-select" required>
                                <option value="Administrator">Administrator</option>
                                <option value="Staff">Staff</option>
                                <option value="Rider">Driver</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" id="editBio" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="editAddress" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="editStatus" class="form-select">
                            <option value="ACTIVATED">Activated</option>
                            <option value="DEACTIVATED">Deactivated</option>
                        </select>
                    </div>

                    <!-- Password reset link (separate action) -->
                    <div class="mb-3 text-end">
                        <small><a href="#" data-bs-dismiss="modal" onclick="alert('Password reset feature coming soon')">Reset Password</a></small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-3">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
    
    <?php if (isset($_SESSION['toast'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast(
                "<?= addslashes($_SESSION['toast']['message']) ?>",
                "<?= $_SESSION['toast']['type'] ?>"
            );
        });
    </script>
    <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../scripts/user_management.js"></script>
    <script type="text/javascript" src="../scripts/notif.js"></script>
    <script type="text/javascript" src="../scripts/sidenav.js"></script>
    <script type="text/javascript" src="../scripts/dropdown2.js"></script>
</body>
</html>