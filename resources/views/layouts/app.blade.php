<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Nova Store')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/products">NOVA STORE</a>
        <div class="navbar-nav ms-auto flex-row gap-3">
            <a class="nav-link" href="/products">Products</a>
            <a class="nav-link" href="/cart">Cart</a>
            <a class="nav-link" href="/orders">Orders</a>
        </div>
    </div>
</nav>
<main class="container py-5">
    @yield('content')
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app-browser.js') }}"></script>
</body>
</html>
