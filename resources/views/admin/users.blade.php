@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">User Management</h2>
        <p class="text-muted mb-0">Create a new user using the backend-supported admin endpoint.</p>
    </div>
</div>

<div id="adminUsersStatus" class="alert alert-info">Checking admin access...</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="alert alert-warning small mb-3">
            No user listing API exists in the current backend. This page only supports creating users through the existing admin endpoint.
        </div>

        <form id="adminUserForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="userName" class="form-label">Name</label>
                    <input type="text" id="userName" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="userEmail" class="form-label">Email</label>
                    <input type="email" id="userEmail" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="userPassword" class="form-label">Password</label>
                    <input type="password" id="userPassword" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="userPasswordConfirmation" class="form-label">Confirm Password</label>
                    <input type="password" id="userPasswordConfirmation" name="password_confirmation" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label for="userRole" class="form-label">Role</label>
                    <select id="userRole" name="role" class="form-select" required>
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection
