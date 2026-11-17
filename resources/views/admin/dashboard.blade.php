@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Admin Dashboard</h2>
        <p class="text-muted mb-0">Administrative operations and inventory monitoring.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Orders</div>
                <h4 class="mt-2 mb-0" id="adminOrderCount">Loading...</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Products</div>
                <h4 class="mt-2 mb-0" id="adminProductCount">Loading...</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Role</div>
                <h4 class="mt-2 mb-0" id="adminRoleBadge">Admin</h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="{{ url('/admin/orders') }}" class="btn btn-primary">Manage Orders</a>
                    <a href="{{ url('/admin/inventory') }}" class="btn btn-success">Inventory Management</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Admin Access</h5>
                <div id="adminStatusBox" class="alert alert-info mb-0">Checking permissions...</div>
            </div>
        </div>
    </div>
</div>
@endsection
