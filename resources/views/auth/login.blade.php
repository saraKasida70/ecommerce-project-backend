```php
@extends('layouts.app')

@section('title', 'Login | Nova Store')

@section('content')

<div class="row justify-content-center align-items-center py-4">
    <div class="col-lg-5 col-md-7 col-sm-10">

        <div class="card border-0 shadow-lg overflow-hidden">

            <div class="text-center text-white p-4"
                 style="background: linear-gradient(135deg, #10213d, #2457d6);">

                <div class="mb-3" style="font-size: 45px;">✦</div>

                <h2 class="fw-bold mb-2">Welcome Back!</h2>

                <p class="mb-0 text-white-50">
                    Sign in to continue your shopping journey.
                </p>
            </div>

            <div class="card-body p-4 p-md-5">

                <h4 class="fw-bold mb-2">Login to your account</h4>

                <p class="text-muted mb-4">
                    Enter your details below.
                </p>

                <form id="loginForm">

                    <div class="mb-4">
                        <label for="loginEmail" class="form-label fw-semibold">
                            Email Address
                        </label>

                        <input
                            type="email"
                            class="form-control form-control-lg"
                            id="loginEmail"
                            placeholder="name@example.com"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="loginPassword" class="form-label fw-semibold">
                            Password
                        </label>

                        <input
                            type="password"
                            class="form-control form-control-lg"
                            id="loginPassword"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary btn-lg w-100 fw-bold">
                        Login →
                    </button>

                </form>

                <div class="text-center mt-4">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="{{ route('register') }}"
                       class="fw-bold text-decoration-none">
                        Create Account
                    </a>
                </div>

                <div class="text-center mt-4">
                    <a href="/products"
                       class="text-muted text-decoration-none small">
                        ← Back to shopping
                    </a>
                </div>

            </div>
        </div>

        <p class="text-center text-muted small mt-4">
            Secure access to your Nova Store account.
        </p>

    </div>
</div>

@endsection
```