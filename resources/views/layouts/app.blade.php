```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Nova Store')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        body {
            background: #f5f7fc;
            color: #17243b;
            font-family: 'Segoe UI', sans-serif;
        }

        .navbar {
            background: #10213d;
            padding: 16px 0;
        }

        .navbar-brand {
            color: white !important;
            font-size: 23px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .navbar .nav-link {
            color: #dce6f7 !important;
            font-weight: 600;
            padding: 9px 12px;
        }

        .navbar .nav-link:hover {
            color: white !important;
        }

        .main-content {
            min-height: 80vh;
            padding-top: 35px;
            padding-bottom: 50px;
        }

        .btn-primary {
            background: #2457d6;
            border-color: #2457d6;
        }

        .btn-primary:hover {
            background: #1945b5;
            border-color: #1945b5;
        }

        .site-footer {
            background: #10213d;
            color: #cbd5e5;
            padding: 25px 0;
            text-align: center;
        }

        @media (max-width: 768px) {
            .navbar-nav {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 5px;
            }

            .navbar .container {
                gap: 12px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">

        <a class="navbar-brand" href="/products">NOVA STORE</a>

        <div class="navbar-nav ms-auto align-items-center">

            <a class="nav-link" href="/products">Home</a>
            <a class="nav-link" href="/products">Products</a>
            <a class="nav-link" href="/cart">Cart</a>
            <a class="nav-link" href="/orders">My Orders</a>

            <span id="navUserBadge" class="text-info d-none">
                <span id="userBadge"></span>
            </span>

            <a id="loginLink" class="nav-link" href="/login">Login</a>

            <a id="registerLink" class="nav-link" href="/register">Register</a>

            <button id="logoutBtn"
                    class="btn btn-outline-light btn-sm ms-2 d-none"
                    type="button">
                Logout
            </button>

        </div>
    </div>
</nav>

<main class="container main-content">

    <div id="appAlert" class="d-none"></div>

    @yield('content')

</main>

<footer class="site-footer">
    <div class="container">
        <p class="mb-1 fw-bold">NOVA STORE</p>
        <small>Quality products. Simple shopping. Better experience.</small>
        <p class="mb-0 mt-2">© {{ date('Y') }} Nova Store. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@vite('resources/js/reverb.js')
<script src="{{ asset('js/app-browser.js') }}"></script>

</body>
</html>
```