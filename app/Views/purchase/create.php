<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>

<body>
    <br><br>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 mx-auto" style="max-width: 1600px;" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>
                <strong>Success!</strong> <?= session()->getFlashdata('success') ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 mx-auto" style="max-width: 1600px;" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>
                <strong>Error!</strong> <?= session()->getFlashdata('error') ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


<div class="container-fluid px-4 py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 mx-auto" style="max-width: 1600px;">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item text-muted">Purchases</li>
                    <li class="breadcrumb-item active fw-medium">New Order</li>
                </ol>
            </nav>
            <h2 class="fw-bold h3 mb-0">Create Purchase Order</h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm px-3" onclick="clearDraft()">
                <i class="bi bi-eraser me-1"></i> Clear Draft
            </button>
           
        </div>
    </div>

    <form id="purchaseForm" action="<?= site_url('purchase/store') ?>" method="POST" class="mx-auto" style="max-width: 1600px;">
        <div class="row g-4">
            
            <div class="col-lg-9">
                <div class="main-card p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-muted">Vendor / Supplier</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shop text-muted"></i></span>
                                <select name="supplier" id="supplier" class="form-select border-start-0" onchange="saveState()" required>
                                    <option value="">Select Supplier...</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= $supplier['id'] ?>"><?= $supplier['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Transaction Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar3 text-muted"></i></span>
                                <input type="date" name="purchase_date" id="purchase_date" class="form-control border-start-0"
                                    value="<?= date('Y-m-d') ?>" onchange="saveState()" required>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">Products</th>
                                    <th style="width: 25%;">Price</th>
                                    <th style="width: 12%;">Quantity</th>
                                    <th style="width: 12%;">VAT</th>
                                    <th class="text-end">Vat Total</th>
                                    <th style="width: 40px;">Total</th>
                                    <th style="width: 40px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">
                                </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-add-row mt-3" onclick="addRow()">
                        <i class="bi bi-plus-circle-dotted me-2"></i> Add Product Row
                    </button>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="summary-card p-4">
                    <h5 class="fw-bold mb-4">Order Summary</h5>
                    
                    <div class="total-row">
                        <span class="text-muted">Sub-total</span>
                        <span class="fw-semibold" id="displayNet">SAR 0.00</span>
                    </div>
                    
                    <div class="total-row">
                        <span class="text-muted">Total VAT</span>
                        <span class="fw-semibold text-danger" id="displayVat">SAR 0.00</span>
                    </div>

                    <div class="grand-total-box text-center">
                        <span class="text-muted d-block small mb-1 uppercase fw-bold">Grand Total</span>
                        <span class="h2 fw-bold text-primary mb-0" id="displayGrand">SAR 0.00</span>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
                        <i class="bi bi-send-check me-2"></i> Confirm Purchase
                    </button>

                    <div class="mt-4 pt-3 border-top text-center">
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle me-1"></i> Data is automatically saved to your local drafts.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
    <script>
        // Data passed from CodeIgniter Controller
        const productsFromDB = <?= json_encode($products) ?>;

       
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>

</html>