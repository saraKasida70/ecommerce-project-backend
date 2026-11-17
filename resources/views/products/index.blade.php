@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Products</h2>
        <p class="text-muted mb-0">Browse available products and add them to your cart.</p>
    </div>
</div>

<div id="productsLoading" class="alert alert-info">Loading products...</div>
<div id="productsContainer" class="row g-4"></div>
@endsection
