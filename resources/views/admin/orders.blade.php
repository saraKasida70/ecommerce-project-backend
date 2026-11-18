@extends('layouts.app')

@section('title', 'Admin Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Admin Orders</h2>
        <p class="text-muted mb-0">Review all orders and update the current status.</p>
    </div>
</div>

<div id="adminOrdersStatus" class="alert alert-info">Loading orders...</div>
<div id="adminOrdersContainer"></div>
@endsection
