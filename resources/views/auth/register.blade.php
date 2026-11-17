@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="mb-3">Register</h2>
                <p class="text-muted">Create a customer account to place orders.</p>

                <form id="registerForm">
                    <div class="mb-3">
                        <label for="registerName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="registerName" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="registerEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="registerPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPasswordConfirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="registerPasswordConfirmation" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Create Account</button>
                </form>

                <p class="mt-3 mb-0 text-center">
                    Already have an account? <a href="{{ url('/login') }}">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
