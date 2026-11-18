```php
@extends('layouts.app')

@section('title', 'Discover Products | Nova Store')

@section('content')
<div class="products-page">

    <section class="products-hero mb-5">
        <div>
            <span class="products-eyebrow">WELCOME TO NOVA STORE</span>
            <h1>Find what you need.</h1>
            <p>
                Explore our collection of quality products
                and discover something you love.
            </p>
        </div>
        <div class="products-hero-icon">✦</div>
    </section>

    <section class="products-section">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="products-heading">Our Products</h2>
                <p class="text-muted mb-0">Explore our latest collection</p>
            </div>
            <a href="/cart" class="btn btn-outline-primary">
                View Cart →
            </a>
        </div>

        <div id="productsLoading" class="alert alert-info">
            Loading products...
        </div>

        <div id="productsContainer" class="row g-4"></div>
    </section>

</div>
@endsection
```