@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="mb-3">Checkout</h2>
                <div id="checkoutItems"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="mb-3">Order Summary</h4>
                <div class="d-flex justify-content-between"><span>Items</span><strong id="checkoutItemCount">0</strong></div>
                <div class="d-flex justify-content-between mt-2"><span>Total</span><strong id="checkoutTotal">$0.00</strong></div>
                <button id="placeOrderBtn" class="btn btn-success w-100 mt-4">Place Order</button>
            </div>
        </div>
    </div>
</div>
@endsection
