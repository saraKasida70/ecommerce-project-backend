@extends('layouts.app')

@section('title', 'Cart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Shopping Cart</h2>
        <p class="text-muted mb-0">Review your selected items before checkout.</p>
    </div>
</div>

<div id="cartContainer"></div>
@endsection
