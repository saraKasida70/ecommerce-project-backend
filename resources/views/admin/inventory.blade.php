@extends('layouts.app')

@section('title', 'Inventory Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Inventory Management</h2>
        <p class="text-muted mb-0">Get stock, add stock, reserve stock, and release stock.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="mb-3">Get Stock</h5>
                <div class="mb-3">
                    <label for="stockProductId" class="form-label">Product ID</label>
                    <input type="number" id="stockProductId" class="form-control" min="1" value="1">
                </div>
                <button type="button" id="getStockBtn" class="btn btn-primary w-100">Check Stock</button>
                <div id="stockResult" class="mt-3 small"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="mb-3">Add Stock</h5>
                <div class="mb-3">
                    <label for="addStockProductId" class="form-label">Product ID</label>
                    <input type="number" id="addStockProductId" class="form-control" min="1" value="1">
                </div>
                <div class="mb-3">
                    <label for="addStockQuantity" class="form-label">Quantity</label>
                    <input type="number" id="addStockQuantity" class="form-control" min="1" value="1">
                </div>
                <button type="button" id="addStockBtn" class="btn btn-success w-100">Add Stock</button>
                <div id="addStockResult" class="mt-3 small"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="mb-3">Reserve / Release</h5>
                <div class="mb-3">
                    <label for="reserveOrderRef" class="form-label">Order Reference</label>
                    <input type="text" id="reserveOrderRef" class="form-control" value="ORDER-TEST-01">
                </div>
                <div class="mb-3">
                    <label for="reserveItemsInput" class="form-label">Items JSON</label>
                    <textarea id="reserveItemsInput" class="form-control" rows="5">[{"product_id":1,"quantity":1}]</textarea>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" id="reserveStockBtn" class="btn btn-warning">Reserve Stock</button>
                    <button type="button" id="releaseStockBtn" class="btn btn-outline-danger">Release Stock</button>
                </div>
                <div id="reserveResult" class="mt-3 small"></div>
            </div>
        </div>
    </div>
</div>
@endsection
