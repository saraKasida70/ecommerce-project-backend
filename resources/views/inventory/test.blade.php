@extends('layouts.app')

@section('title', 'Inventory Testing')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Inventory Testing</h2>
        <p class="text-muted mb-0">Test real backend inventory operations and inspect live errors.</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="testProductId" class="form-label">Product ID</label>
                <input type="number" id="testProductId" class="form-control" value="1" min="1">
            </div>
            <div class="col-md-4">
                <label for="testQuantity" class="form-label">Quantity</label>
                <input type="number" id="testQuantity" class="form-control" value="1" min="1">
            </div>
            <div class="col-md-4">
                <label for="testRef" class="form-label">Order Reference</label>
                <input type="text" id="testRef" class="form-control" value="ORDER-TEST-01">
            </div>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="button" id="testGetStockBtn" class="btn btn-primary">Get Stock</button>
            <button type="button" id="testAddStockBtn" class="btn btn-success">Add Stock</button>
            <button type="button" id="testReserveBtn" class="btn btn-warning">Reserve Stock</button>
            <button type="button" id="testReleaseBtn" class="btn btn-outline-danger">Release Stock</button>
        </div>

        <div id="inventoryTestResult" class="mt-4 alert alert-light border">No operation has been performed yet.</div>
    </div>
</div>
@endsection
