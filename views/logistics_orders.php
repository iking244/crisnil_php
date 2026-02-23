 <!-- AKA Job Order -->
 <?php
    include "../controllers/logistics_orders_controller.php";
    ?>

 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Orders - CRISNIL TRADING CORPORATION</title>
     <link rel="stylesheet" href="../styles/logistics.css">
     <link rel="stylesheet" href="../styles/orders.css">
     <link rel="stylesheet" href="../styles/dashboard2.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
 </head>

 <body>
     <?php include '../includes/header.php'; ?>
     <?php include '../includes/sidenav.php'; ?>
     <?php include '../includes/status_helper.php'; ?>
     <?php if (isset($_GET['msg'])): ?>
         <?php if ($_GET['msg'] === 'order_cancelled'): ?>
             <div class="alert alert-warning">
                 Job order has been cancelled.
             </div>
         <?php elseif ($_GET['msg'] === 'order_deleted'): ?>
             <div class="alert alert-success">
                 Job order deleted successfully.
             </div>
         <?php endif; ?>
     <?php endif; ?>

     <?php if (isset($_GET['error']) && $_GET['error'] === 'delete_not_allowed'): ?>
         <div class="alert alert-danger">
             This job order cannot be cancelled or deleted.
         </div>
     <?php endif; ?>


     <main class="main-content p-4">
         <div class="container-fluid">
             <div class="d-flex justify-content-between align-items-center mb-3">
                 <h1 class="page-title">Orders</h1>
                 <button class="export-btn btn btn-primary">
                     <i class="fa fa-download"></i> Export
                 </button>
             </div>

             <!-- Search -->
             <div class="search-wrapper mb-4">
                 <input type="text" id="searchInput" class="search-input"
                     placeholder="Search by ID, Origin, or Destination...">
                 <i class="fa fa-search search-icon"></i>
             </div>

             <!-- Table -->
             <div class="table-container">
                 <table class="orders-table" id="ordersTable">
                     <thead>
                         <tr>
                             <th><input type="checkbox" id="selectAll"></th>
                             <th>ID</th>
                             <th>Origin</th>
                             <th>Destination</th>
                             <th>Status</th>
                             <th>Created At</th>
                             <th>ETA</th>
                             <th>Action</th>
                         </tr>
                     </thead>
                     <tbody>
                         <?php while ($row = $orders->fetch_assoc()): ?>
                             <tr>
                                 <td><input type="checkbox" class="row-check"></td>
                                 <td><strong>#<?= $row['id'] ?></strong></td>
                                 <td><?= htmlspecialchars($row['origin']) ?></td>
                                 <td><?= htmlspecialchars($row['destination']) ?></td>
                                 <td><?= renderStatusBadge($row['status']) ?></td>
                                 <td><?= date('Y-m-d h:i A', strtotime($row['created_at'])) ?></td>
                                 <td><?= $row['eta'] ? date('H:i', strtotime($row['eta'])) : 'N/A' ?></td>
                                 <td>
                                     <i class="fa fa-pencil edit-icon"
                                         data-id="<?= $row['id'] ?>"
                                         data-status="<?= $row['status'] ?>"
                                         title="Edit"></i>

                                     <i class="fa fa-trash text-danger ms-2 delete-icon"
                                         data-id="<?= $row['id'] ?>"
                                         data-status="<?= $row['status'] ?>"
                                         title="Delete"></i>

                                     <a href="order_details_new.php?order_id=<?= $row['id'] ?>"
                                         class="btn btn-sm btn-info" style="margin-left:12px; background-color:#d1484c;">
                                         <i class="fas fa-eye"></i> Details
                                     </a>
                                 </td>
                             </tr>
                         <?php endwhile; ?>
                     </tbody>
                 </table>
             </div>

             <div class="card-footer mt-4">
                 <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                     <a href="?page=<?= $i ?>"
                         class="view-btn"
                         style="<?= ($i == $page) ? 'background:#1e3fa3;color:white;' : '' ?>">
                         <?= $i ?>
                     </a>
                 <?php endfor; ?>
             </div>

             <!-- Floating Add Button -->
             <button class="floating-add-btn"
                 data-bs-toggle="modal"
                 data-bs-target="#createOrderModal">
                 <i class="fa fa-plus"></i>
             </button>
         </div>
     </main>

     <!-- =========================
     CREATE JOB ORDER MODAL
========================= -->
     <div class="modal fade" id="createOrderModal" tabindex="-1">
         <div class="modal-dialog modal-lg">
             <br><br><br><br><br>
             <div class="modal-content">
                 <div class="modal-header">
                     <h5 class="modal-title">Create New Job Order</h5>
                     <button type="button" class="btn-close"
                         data-bs-dismiss="modal"></button>
                 </div>

                 <div class="modal-body">
                     <form action="../controllers/logistics_orders_controller.php?action=create"
                         method="POST">

                         <div class="row">
                             <div class="col-md-6 mb-3">
                                 <label class="form-label">Origin Warehouse</label>
                                 <select name="warehouse_id" class="form-select" required>
                                     <option value="">Select Warehouse</option>
                                     <?php while ($w = $warehouses->fetch_assoc()): ?>
                                         <option value="<?= $w['warehouse_id'] ?>">
                                             <?= $w['warehouse_name'] ?>
                                         </option>
                                     <?php endwhile; ?>
                                 </select>
                             </div>

                             <div class="col-md-6 mb-3">
                                 <label class="form-label">Destination Client</label>
                                 <select name="client_id" class="form-select" required>
                                     <option value="">Select Client</option>
                                     <?php while ($c = $clients->fetch_assoc()): ?>
                                         <option value="<?= $c['client_id'] ?>">
                                             <?= $c['client_name'] ?>
                                         </option>
                                     <?php endwhile; ?>
                                 </select>
                             </div>
                         </div>

                         <h6 class="mt-3">Cargo Details</h6>

                         <div class="table-responsive">
                             <table class="table table-bordered" id="createItemsTable">
                                 <thead class="table-light">
                                     <tr>
                                         <th>Product</th>
                                         <th width="120">Quantity</th>
                                         <th width="80">Action</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <tr>
                                         <td>
                                             <select name="product_id[]" class="form-control">
                                                 <?php
                                                    mysqli_data_seek($products, 0);
                                                    while ($p = $products->fetch_assoc()):
                                                    ?>
                                                     <option value="<?= $p['product_id'] ?>">
                                                         <?= $p['product_name'] ?> (<?= $p['unit'] ?>)
                                                     </option>
                                                 <?php endwhile; ?>
                                             </select>
                                         </td>
                                         <td>
                                             <input type="number"
                                                 name="quantity[]"
                                                 class="form-control"
                                                 value="1" min="1">
                                         </td>
                                         <td class="text-center">
                                             <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove">
                                                 <i class="fas fa-trash-alt"></i>
                                             </button>
                                         </td>
                                     </tr>
                                 </tbody>
                             </table>
                         </div>

                         <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow('createItemsTable')">
                             + Add Item
                         </button>

                         <button type="submit"
                             class="btn btn-success w-100 mt-3">
                             Create Job Order
                         </button>
                     </form>
                 </div>
             </div>
         </div>
     </div>

     <!-- =========================
     EDIT JOB ORDER MODAL
========================= -->
     <div class="modal fade" id="editOrderModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-lg modal-dialog-centered">
             <div class="modal-content">

                 <div class="modal-header">
                     <h5 class="modal-title">
                         Edit Job Order #<span id="editJobIdDisplay"></span>
                     </h5>
                     <button type="button" class="btn-close"
                         data-bs-dismiss="modal"></button>
                 </div>

                 <div class="modal-body">

                     <form action="../controllers/logistics_orders_controller.php?action=update"
                         method="POST">

                         <input type="hidden" id="editJobId" name="id">

                         <div class="row">
                             <div class="col-md-6 mb-3">
                                 <label class="form-label">Origin</label>
                                 <input type="text" id="editOrigin"
                                     class="form-control" readonly>
                             </div>

                             <div class="col-md-6 mb-3">
                                 <label class="form-label">Destination</label>
                                 <input type="text" id="editDestination"
                                     class="form-control" readonly>
                             </div>
                         </div>

                         <div class="row">
                             <div class="col-md-4 mb-3">
                                 <label class="form-label">Status</label>
                                 <select id="editStatus"
                                     name="status"
                                     class="form-select">
                                     <option value="pending">Pending</option>
                                     <option value="cancelled">Cancelled</option>
                                 </select>
                             </div>

                             <div class="col-md-4 mb-3">
                                 <label class="form-label">Created At</label>
                                 <input type="text"
                                     id="editCreatedAt"
                                     class="form-control" readonly>
                             </div>

                             <div class="col-md-4 mb-3">
                                 <label class="form-label">ETA</label>
                                 <input type="datetime-local"
                                     id="editETA"
                                     name="eta"
                                     class="form-control">
                             </div>
                         </div>

                         <hr>

                         <h6 class="mb-3">Job Order Items</h6>

                         <div class="table-responsive">
                             <table class="table table-bordered align-middle"
                                 id="itemsTable">

                                 <thead class="table-light">
                                     <tr>
                                         <th>Product</th>
                                         <th width="150">Available Stock</th>
                                         <th width="120">Quantity</th>
                                         <th width="80" class="text-center">Action</th>
                                     </tr>
                                 </thead>

                                 <tbody></tbody>

                             </table>
                         </div>

                         <div class="d-flex justify-content-between mt-2">
                             <button type="button"
                                 class="btn btn-sm btn-outline-primary"
                                 onclick="addItemRow()">
                                 + Add Item
                             </button>

                             <button type="submit"
                                 class="btn btn-primary">
                                 Save Changes
                             </button>
                         </div>

                     </form>

                 </div>
             </div>
         </div>
     </div>
     <!-- =========================
     PRODUCT LIST FOR JS
========================= -->
     <script>
         window.productList = [
             <?php
                mysqli_data_seek($products, 0);
                while ($p = $products->fetch_assoc()):
                ?> {
                     id: <?= $p['product_id'] ?>,
                     name: "<?= addslashes($p['product_name']) ?>",
                     unit: "<?= $p['unit'] ?>"
                 },
             <?php endwhile; ?>
         ];
     </script>

     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
     <script src="../scripts/table.js"></script>
     <script src="../scripts/orders.js"></script>
     <script src="../scripts/notif.js"></script>
     <script src="../scripts/sidenav.js"></script>
     <script src="../scripts/dropdown2.js"></script>

     <script>
         // When create modal opens → add one row if empty
         document.getElementById('createModal').addEventListener('shown.bs.modal', function() {
             var tbody = document.getElementById('createItemsTable').getElementsByTagName('tbody')[0];
             if (tbody.rows.length === 0) {
                 addItemRow('createItemsTable');
             }
         });
     </script>

 </body>

 </html>