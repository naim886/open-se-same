@extends('frontend.layouts.app')

@section('title', 'Contact Us – Open Se Same')
@section('meta_description', 'Get in touch with Open Se Same. We\'d love to hear from you — questions, feedback, or just a hello!')
@section('og_title', 'Contact Us – Open Se Same')
@section('og_description', 'Get in touch with Open Se Same. We\'d love to hear from you — questions, feedback, or just a hello!')
@section('robots', 'index, follow')

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
echo Schema::contactPage()
    ->name('Contact Us - Open Se Same')
    ->description('Get in touch with Open Se Same. We\'d love to hear from you — questions, feedback, or just a hello!')
    ->url(route('shop.contact'))
    ->toScript();
echo Schema::breadcrumbList()->itemListElement([
    Schema::listItem()->position(1)->name('Home')->item(route('shop.home')),
    Schema::listItem()->position(2)->name('Contact Us')->item(route('shop.contact')),
])->toScript();
@endphp
@endsection

@section('content')

{{-- Hero Banner --}}
<div style="background:var(--primary-gradient); padding:52px 0 44px; text-align:center; position:relative; overflow:hidden;">
    <div style="position:absolute;inset:0;background:url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 20%22><path d=%22M0,10 Q25,20 50,10 Q75,0 100,10 L100,20 L0,20Z%22 fill=%22rgba(255,255,255,0.04)%22/></svg>') bottom/100% auto no-repeat;"></div>
    <div class="container" style="position:relative;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb justify-content-center" style="margin:0; padding:0; background:transparent;">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}" style="color:rgba(255,255,255,0.75); text-decoration:none;">Home</a></li>
                <li class="breadcrumb-item active" style="color:#fff;">Contact Us</li>
            </ol>
        </nav>
        <h1 class="fw-bold mb-2" style="color:#fff; font-size:32px; letter-spacing:-0.5px;">Contact Us</h1>
        <p style="color:rgba(255,255,255,0.78); font-size:15px; margin:0;">We'd love to hear from you — reach out anytime!</p>
    </div>
</div>

<section class="py-5" style="background:var(--bg-light);">
<div class="container">

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert d-flex align-items-center gap-3 mb-4"
             style="background:#e8f8f1; border:1.5px solid #a3e4c5; border-radius:var(--radius); color:#1a6640; font-size:14.5px;">
            <i class="fas fa-check-circle fa-lg" style="color:#27ae60; flex-shrink:0;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="row g-5 align-items-start">

        {{-- ── Left: Info Cards ────────────────────────── --}}
        <div class="col-lg-4">

            {{-- Intro text --}}
            <h2 class="fw-bold mb-2" style="color:var(--text-dark); font-size:22px;">Get in Touch</h2>
            <p style="color:var(--text-muted); font-size:14.5px; line-height:1.7; margin-bottom:28px;">
                Have a question about an order, a product, or just want to say hello?
                Fill out the form and our team will get back to you as soon as possible.
            </p>

            {{-- Info cards --}}
            <div class="d-flex flex-column gap-3">

                <div class="d-flex align-items-start gap-3 p-3" style="background:#fff; border-radius:var(--radius); border:1.5px solid var(--border); box-shadow:var(--shadow);">
                    <div style="width:42px; height:42px; border-radius:50%; background:var(--primary-gradient); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas fa-phone" style="color:#fff; font-size:16px;"></i>
                    </div>
                    <div>
                        <div style="font-weight:600; font-size:13px; color:var(--text-dark); margin-bottom:3px;">Phone / WhatsApp</div>
                        <a href="tel:+8801886870406" style="color:var(--primary); font-size:14px; font-weight:500; text-decoration:none; display:block;">
                            +880 1886-870406
                        </a>
                        <a href="https://wa.me/+8801886870406" target="_blank"
                           style="color:var(--text-muted); font-size:13px; text-decoration:none;">
                           <i class="fab fa-whatsapp me-1" style="color:#25d366;"></i>Chat on WhatsApp
                        </a>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3" style="background:#fff; border-radius:var(--radius); border:1.5px solid var(--border); box-shadow:var(--shadow);">
                    <div style="width:42px; height:42px; border-radius:50%; background:var(--primary-gradient); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fab fa-facebook-f" style="color:#fff; font-size:16px;"></i>
                    </div>
                    <div>
                        <div style="font-weight:600; font-size:13px; color:var(--text-dark); margin-bottom:3px;">Facebook</div>
                        <a href="https://www.facebook.com/opensesame1" target="_blank"
                           style="color:var(--primary); font-size:14px; font-weight:500; text-decoration:none;">
                            facebook.com/opensesame1
                        </a>
                        <br>
                        <a href="https://m.me/opensesame1" target="_blank"
                           style="color:var(--text-muted); font-size:13px; text-decoration:none;">
                           <i class="fab fa-facebook-messenger me-1" style="color:#0084ff;"></i>Message us
                        </a>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3" style="background:#fff; border-radius:var(--radius); border:1.5px solid var(--border); box-shadow:var(--shadow);">
                    <div style="width:42px; height:42px; border-radius:50%; background:var(--primary-gradient); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas fa-clock" style="color:#fff; font-size:16px;"></i>
                    </div>
                    <div>
                        <div style="font-weight:600; font-size:13px; color:var(--text-dark); margin-bottom:3px;">Business Hours</div>
                        <p style="color:var(--text-muted); font-size:13px; margin:0; line-height:1.7;">
                            <strong style="color:var(--text-dark);">24/7 &mdash; 365 Days a Year</strong><br>
                            Always here for you!
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3" style="background:#fff; border-radius:var(--radius); border:1.5px solid var(--border); box-shadow:var(--shadow);">
                    <div style="width:42px; height:42px; border-radius:50%; background:var(--primary-gradient); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas fa-truck" style="color:#fff; font-size:16px;"></i>
                    </div>
                    <div>
                        <div style="font-weight:600; font-size:13px; color:var(--text-dark); margin-bottom:3px;">Delivery Info</div>
                        <p style="color:var(--text-muted); font-size:13px; margin:0; line-height:1.7;">
                            Inside Dhaka: <strong style="color:var(--text-dark);">৳70</strong><br>
                            Outside Dhaka: <strong style="color:var(--text-dark);">৳120</strong><br>
                            Express (Dhaka): <strong style="color:var(--text-dark);">৳150</strong>
                        </p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Right: Contact Form ──────────────────────── --}}
        <div class="col-lg-8">
            <div class="p-4 p-md-5" style="background:#fff; border-radius:16px; border:1.5px solid var(--border); box-shadow:var(--shadow-hover);">

                <h3 class="fw-bold mb-1" style="color:var(--text-dark); font-size:20px;">Send Us a Message</h3>
                <p style="color:var(--text-muted); font-size:13.5px; margin-bottom:28px;">
                    We're available 24/7 &mdash; we typically reply within a few hours.
                </p>

                <form method="POST" action="{{ route('shop.contact.submit') }}" id="contact-form" novalidate>
                    @csrf

                    <div class="row g-3">

                        {{-- Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600" style="font-size:13.5px; color:var(--text-dark); font-weight:600;">
                                Full Name <span style="color:var(--primary);">*</span>
                            </label>
                            <div class="input-group-custom">
                                <span class="input-icon"><i class="fas fa-user"></i></span>
                                <input type="text" name="name" value="{{ old('name') }}"
                                       class="form-control contact-input @error('name') is-invalid @enderror"
                                       placeholder="Your full name" required>
                            </div>
                            @error('name')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13.5px; color:var(--text-dark); font-weight:600;">
                                Email Address <span style="color:var(--primary);">*</span>
                            </label>
                            <div class="input-group-custom">
                                <span class="input-icon"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       class="form-control contact-input @error('email') is-invalid @enderror"
                                       placeholder="your@email.com" required>
                            </div>
                            @error('email')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13.5px; color:var(--text-dark); font-weight:600;">
                                Phone Number <span style="color:var(--text-muted); font-weight:400;">(optional)</span>
                            </label>
                            <div class="input-group-custom">
                                <span class="input-icon"><i class="fas fa-phone"></i></span>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                       class="form-control contact-input @error('phone') is-invalid @enderror"
                                       placeholder="01XXXXXXXXX">
                            </div>
                            @error('phone')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Subject --}}
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13.5px; color:var(--text-dark); font-weight:600;">
                                Subject <span style="color:var(--primary);">*</span>
                            </label>
                            <div class="input-group-custom">
                                <span class="input-icon"><i class="fas fa-tag"></i></span>
                                <input type="text" name="subject" value="{{ old('subject') }}"
                                       class="form-control contact-input @error('subject') is-invalid @enderror"
                                       placeholder="How can we help?" required>
                            </div>
                            @error('subject')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Message --}}
                        <div class="col-12">
                            <label class="form-label" style="font-size:13.5px; color:var(--text-dark); font-weight:600;">
                                Message <span style="color:var(--primary);">*</span>
                            </label>
                            <textarea name="message" rows="6"
                                      class="form-control contact-input @error('message') is-invalid @enderror"
                                      placeholder="Write your message here…" required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="col-12 mt-1">
                            <button type="submit" id="send-btn"
                                    style="background:var(--primary-gradient); color:#fff; border:none;
                                           border-radius:50px; padding:14px 40px; font-size:15px;
                                           font-weight:600; cursor:pointer; transition:var(--transition);
                                           display:inline-flex; align-items:center; gap:8px;"
                                    onmouseover="this.style.boxShadow='0 8px 28px rgba(122,18,53,0.35)'; this.style.transform='translateY(-1px)'"
                                    onmouseout="this.style.boxShadow='none'; this.style.transform='none'">
                                <i class="fas fa-paper-plane"></i>
                                <span>Send Message</span>
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
</section>

@endsection

@section('extra_css')
<style>
    /* Input Group with Icon */
    .input-group-custom {
        position: relative;
    }
    .input-group-custom .input-icon {
        position: absolute;
        left: 14px; top: 50%; transform: translateY(-50%);
        color: var(--text-muted); font-size: 14px; pointer-events: none; z-index: 2;
    }
    .input-group-custom .contact-input {
        padding-left: 40px;
    }
    /* Input Styles */
    .contact-input {
        border: 1.5px solid var(--border);
        border-radius: var(--radius);
        padding: 12px 16px;
        font-size: 14px;
        color: var(--text-dark);
        transition: var(--transition);
        background: var(--bg-light);
        width: 100%;
    }
    .contact-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(194,67,107,0.1);
        background: #fff;
    }
    .contact-input.is-invalid {
        border-color: #dc3545;
    }
    textarea.contact-input { resize: vertical; min-height: 140px; }
    /* Validation message */
    .invalid-msg {
        color: #dc3545;
        font-size: 12.5px;
        margin-top: 4px;
    }
</style>
@endsection

@section('extra_js')
<script>
// ── Meta Pixel: Lead (contact form submit) + Contact (phone/WA click) ───
document.addEventListener('DOMContentLoaded', function () {
    var cf = document.getElementById('contact-form');
    if (cf) {
        cf.addEventListener('submit', function () {
            if (typeof fbq === 'function') {
                fbq('track', 'Lead',    { content_name: 'Contact Form' });
                fbq('track', 'Contact', { content_name: 'Contact Form' });
            }
        });
    }
    // Contact event on phone / WhatsApp / Messenger clicks
    document.querySelectorAll('a[href^="tel:"], a[href^="https://wa.me"], a[href^="https://m.me"]').forEach(function(el) {
        el.addEventListener('click', function () {
            if (typeof fbq === 'function') {
                fbq('track', 'Contact', { content_name: el.getAttribute('href') });
            }
        });
    });
});
</script>
@endsection
