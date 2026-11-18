@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">My Orders</h2>
        <p class="text-muted mb-0">Orders owned by the current authenticated customer.</p>
    </div>
</div>

<div id="ordersLoading" class="alert alert-info">Loading your orders...</div>
<div id="ordersContainer"></div>
@endsection
