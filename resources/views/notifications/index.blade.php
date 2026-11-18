@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Notifications</h2>
        <p class="text-muted mb-0">View your current notifications and mark them as read.</p>
    </div>
    <button type="button" id="markAllNotificationsRead" class="btn btn-outline-primary btn-sm">Mark all as read</button>
</div>

<div id="notificationsStatus" class="alert alert-info">Loading notifications...</div>
<div id="notificationsContainer"></div>
@endsection
