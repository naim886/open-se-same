@extends('emails.layouts.app')

@section('content')
    <h4><strong>Hi {{ $name ?? null }}, </strong></h4>
    <p><strong class="theme-text">Welcome to OpenSesame BD!</strong> First of all a <strong class="theme-text">HUGE</strong> thank you for registering with <strong class="theme-text">OpenSesame BD!</strong> We are beyond excited to welcome you into our family.</p>
    <p>At OpenSesame BD, you’re not just signing up — you’re stepping into a world of exclusive products, handpicked just for you. </p>
    <p>From unique designs to premium quality, everything you find here is meant to make you feel truly special.</p>
    <p>We believe fashion is a way to express your inner muse, and with our carefully curated collections, you’ll always have something extraordinary to showcase.</p>
    <p>Once again, thank you so, so much for trusting us. Stay tuned — something beautiful is always waiting for you at OpenSesame BD!</p>
    <p>Best regards, <br> <strong class="theme-text">The OpenSesame BD Team</strong></p>
@endsection
