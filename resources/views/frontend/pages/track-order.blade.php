@extends('frontend.layouts.app')

@section('title', 'Track Your Order – Open Se Same')
@section('meta_description', 'Track your Open Se Same order in real-time. Enter your phone number or order number to see live status updates.')
@section('og_title', 'Track Your Order – Open Se Same')
@section('og_description', 'Track your Open Se Same order in real-time. Enter your phone number or order number to see live status updates.')
@section('og_image', asset('images/track-og.webp'))
@section('robots', 'noindex, follow')

@section('content')

{{-- Hero Banner --}}
<div style="background:var(--primary-gradient); padding:52px 0 44px; text-align:center; position:relative; overflow:hidden;">
    <div style="position:absolute;inset:0;background:url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 20%22><path d=%22M0,10 Q25,20 50,10 Q75,0 100,10 L100,20 L0,20Z%22 fill=%22rgba(255,255,255,0.04)%22/></svg>') bottom/100% auto no-repeat;"></div>
    <div class="container" style="position:relative;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb justify-content-center" style="margin:0;padding:0;background:transparent;">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}" style="color:rgba(255,255,255,0.75);text-decoration:none;">Home</a></li>
                <li class="breadcrumb-item active" style="color:#fff;">Track Order</li>
            </ol>
        </nav>
        <h1 class="fw-bold mb-2" style="color:#fff;font-size:32px;letter-spacing:-0.5px;">
            <i class="fas fa-map-marker-alt me-2" style="opacity:.85;"></i>Track Your Order
        </h1>
        <p style="color:rgba(255,255,255,0.78);font-size:15px;margin:0;">Enter your phone number or order number to see real-time status</p>
    </div>
</div>

<section class="py-5" style="background:var(--bg-light);">
<div class="container">

    {{-- Search Form --}}
    <div class="row justify-content-center mb-5">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0" style="border-radius:var(--radius);box-shadow:var(--shadow);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1" style="color:var(--text-dark);font-size:17px;">
                        <i class="fas fa-search me-2" style="color:var(--primary);"></i>Find Your Order
                    </h5>
                    <p class="mb-3" style="font-size:13.5px;color:var(--text-muted);">Enter the phone number used at checkout, or your order number</p>
                    <form method="GET" action="{{ route('shop.track-order') }}">
                        <div class="input-group" style="border-radius:var(--radius);overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);">
                            <span class="input-group-text" style="background:#fff;border:1.5px solid var(--border);border-right:none;color:var(--text-muted);padding:0 14px;">
                                <i class="fas fa-mobile-alt"></i>
                            </span>
                            <input type="text" name="q"
                                   value="{{ $query }}"
                                   class="form-control"
                                   placeholder="Phone number or order number…"
                                   style="border:1.5px solid var(--border);border-left:none;border-right:none;font-size:14.5px;padding:11px 14px;outline:none;box-shadow:none;">
                            <button type="submit" class="btn px-4 fw-600"
                                    style="background:var(--primary-gradient);color:#fff;border:none;font-size:14px;letter-spacing:.3px;border-radius:0;">
                                <i class="fas fa-search me-1"></i> Track
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($searched)

        @if($orders->isEmpty())

            {{-- Not Found --}}
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8 text-center">
                    <div class="card border-0 py-5" style="border-radius:var(--radius);box-shadow:var(--shadow);">
                        <div class="card-body">
                            <div style="width:72px;height:72px;background:linear-gradient(135deg,#dcf5ea,#c3ecd9);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                                <i class="fas fa-search" style="font-size:28px;color:var(--primary);"></i>
                            </div>
                            <h5 class="fw-bold mb-2" style="color:var(--text-dark);">No Order Found</h5>
                            <p style="color:var(--text-muted);font-size:14px;margin:0;">
                                We couldn't find any order for <strong>{{ $query }}</strong>.<br>
                                Please check your phone number or order number and try again.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        @else

            @foreach($orders as $order)
            <div class="card border-0 mb-4" style="border-radius:var(--radius);box-shadow:var(--shadow);">

                {{-- Order Header --}}
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 py-3 px-4"
                     style="background:linear-gradient(135deg,#f3faf6,#eaf7f1);border-bottom:1.5px solid var(--border);border-radius:var(--radius) var(--radius) 0 0;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;background:var(--primary-gradient);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-box" style="color:#fff;font-size:17px;"></i>
                        </div>
                        <div>
                            <div class="fw-bold d-flex align-items-center gap-2" style="font-size:15.5px;color:var(--text-dark);">
                                Order #{{ $order->order_number }}
                                @if((float)$order->paid_amount >= (float)$order->total_amount && $order->total_amount > 0)
                                <span style="background:#d4edda;color:#155724;font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.4px;">
                                    <i class="fas fa-check-circle me-1"></i>PAID
                                </span>
                                @endif
                            </div>
                            <div style="font-size:12.5px;color:var(--text-muted);">
                                <i class="fas fa-calendar-alt me-1"></i>{{ $order->order_date?->format('d M Y, h:i A') }}
                            </div>
                        </div>
                    </div>
                    <span class="badge px-3 py-2" style="font-size:13px;font-weight:600;
                        background:{{ match($order->status) {
                            \App\Models\Order::STATUS_DELIVERED => '#d4edda',
                            \App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_RETURNED => '#f8d7da',
                            \App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_COURIER_ENTRY => '#d6d8db',
                            \App\Models\Order::STATUS_CONFIRMED, \App\Models\Order::STATUS_COURIER_PENDING => '#d1ecf1',
                            default => '#fff3cd',
                        } }};
                        color:{{ match($order->status) {
                            \App\Models\Order::STATUS_DELIVERED => '#155724',
                            \App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_RETURNED => '#721c24',
                            \App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_COURIER_ENTRY => '#383d41',
                            \App\Models\Order::STATUS_CONFIRMED, \App\Models\Order::STATUS_COURIER_PENDING => '#0c5460',
                            default => '#856404',
                        } }};
                        border-radius:8px;">
                        <i class="fas fa-circle me-1" style="font-size:8px;vertical-align:middle;"></i>
                        {{ $order->getStatusText() }}
                    </span>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">

                        {{-- Left: Order Details --}}
                        <div class="col-lg-5">
                            <h6 class="fw-bold mb-3" style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;border-bottom:1.5px solid var(--border);padding-bottom:8px;">
                                <i class="fas fa-info-circle me-1"></i>Order Details
                            </h6>

                            <div class="d-flex flex-column gap-2" style="font-size:14px;">
                                <div class="d-flex justify-content-between">
                                    <span style="color:var(--text-muted);">Customer</span>
                                    <span class="fw-600" style="color:var(--text-dark);">{{ $order->customer_name }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span style="color:var(--text-muted);">Phone</span>
                                    <span class="fw-600" style="color:var(--text-dark);font-family:monospace;">
                                        {{ $order->masked_phone }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <span style="color:var(--text-muted);white-space:nowrap;">Address</span>
                                    <span class="fw-600 text-end" style="color:var(--text-dark);">{{ $order->masked_address }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span style="color:var(--text-muted);">Delivery</span>
                                    <span class="fw-600" style="color:var(--text-dark);">{{ \App\Models\Order::DELIVERY_LIST[$order->delivery_location] ?? '—' }}</span>
                                </div>
                                <div class="d-flex justify-content-between" style="border-top:1.5px solid var(--border);padding-top:8px;margin-top:4px;">
                                    <span style="color:var(--text-muted);">Products</span>
                                    <span class="fw-600">৳{{ number_format($order->product_price, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span style="color:var(--text-muted);">Delivery Charge</span>
                                    <span class="fw-600">৳{{ number_format($order->delivery_charge, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between" style="border-top:1.5px solid var(--border);padding-top:8px;margin-top:4px;">
                                    <span class="fw-bold" style="color:var(--text-dark);">Total</span>
                                    <span class="fw-bold" style="color:var(--primary);font-size:16px;">৳{{ number_format($order->total_amount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span style="color:var(--text-muted);">Payment</span>
                                    @if((float)$order->paid_amount >= (float)$order->total_amount && $order->total_amount > 0)
                                        <span style="background:#d4edda;color:#155724;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">
                                            <i class="fas fa-check-circle me-1"></i>Paid in Full
                                        </span>
                                    @elseif($order->paid_amount > 0)
                                        <span style="background:#fff3cd;color:#856404;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">
                                            <i class="fas fa-clock me-1"></i>Partially Paid (৳{{ number_format($order->paid_amount, 2) }})
                                        </span>
                                    @else
                                        <span style="background:#f8d7da;color:#721c24;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">
                                            <i class="fas fa-times-circle me-1"></i>Unpaid
                                        </span>
                                    @endif
                                </div>
                                @if($order->tracking_code)
                                <div class="d-flex justify-content-between align-items-center" style="border-top:1.5px solid var(--border);padding-top:8px;margin-top:4px;">
                                    <span style="color:var(--text-muted);">Tracking Code</span>
                                    <span class="badge bg-dark px-2 py-1" style="font-family:monospace;font-size:12.5px;">{{ $order->tracking_code }}</span>
                                </div>
                                @endif
                            </div>

                            {{-- Order Items --}}
                            @if($order->orderItems->count() > 0)
                            <h6 class="fw-bold mt-4 mb-3" style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;border-bottom:1.5px solid var(--border);padding-bottom:8px;">
                                <i class="fas fa-shopping-bag me-1"></i>Items ({{ $order->orderItems->count() }})
                            </h6>
                            <div class="d-flex flex-column gap-2">
                                @foreach($order->orderItems as $item)
                                <div class="d-flex justify-content-between align-items-center p-2" style="background:#fafafa;border-radius:8px;border:1px solid var(--border);">
                                    <div class="d-flex align-items-center gap-2">
                                        @php
                                            $itemThumb = $item->product?->primary_photo_thumb
                                                ? asset('storage/'.$item->product->primary_photo_thumb)
                                                : null;
                                        @endphp
                                        @if($itemThumb)
                                        <a href="{{ $item->product?->slug ? route('shop.product', $item->product->slug) : '#' }}" target="_blank" style="flex-shrink:0;">
                                            <img src="{{ $itemThumb }}"
                                                 alt="{{ $item->product->name }}"
                                                 loading="lazy"
                                                 style="width:44px;height:44px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
                                        </a>
                                        @else
                                        <div style="width:44px;height:44px;background:#f0f0f0;border-radius:6px;border:1px solid var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-image" style="color:#ccc;font-size:16px;"></i>
                                        </div>
                                        @endif
                                        <div style="font-size:13.5px;color:var(--text-dark);">
                                            @if($item->product?->slug)
                                            <a href="{{ route('shop.product', $item->product->slug) }}" target="_blank" class="fw-600" style="color:var(--text-dark);text-decoration:none;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dark)'">{{ $item->product->name }}</a>
                                            @else
                                            <span class="fw-600">{{ $item->product?->name ?? 'Product #'.$item->product_id }}</span>
                                            @endif
                                            @if($item->variation_label)
                                                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ $item->variation_label }}</div>
                                            @endif
                                            <span style="color:var(--text-muted);margin-left:6px;">× {{ $item->quantity }}</span>
                                        </div>
                                    </div>
                                    <span style="font-size:13.5px;color:var(--text-dark);font-weight:600;">৳{{ number_format($item->subtotal, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        {{-- Right: Status Timeline --}}
                        <div class="col-lg-4">
                            <h6 class="fw-bold mb-3" style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;border-bottom:1.5px solid var(--border);padding-bottom:8px;">
                                <i class="fas fa-history me-1"></i>Status History
                            </h6>

                            @php
                                $timeline = $order->histories
                                    ->sortByDesc('created_at')
                                    ->values();
                                $isLast = $timeline->count() - 1;
                            @endphp

                            @if($timeline->isEmpty())
                                <p class="text-muted" style="font-size:13.5px;">No status history available.</p>
                            @else
                                <div class="position-relative ps-3" style="border-left:2px solid var(--border);">
                                    @foreach($timeline as $idx => $history)
                                    @php
                                        $isCreate = $history->action === 'created';
                                        $statusChange = isset($history->changes['status']);
                                        $newStatus = $statusChange ? ($history->changes['status']['to'] ?? null) : null;
                                        $label = $isCreate
                                            ? 'Order Placed'
                                            : ($statusChange
                                                ? (\App\Models\Order::STATUS_LIST[$newStatus] ?? 'Updated')
                                                : 'Order Updated');
                                        $dotColor = match(true) {
                                            $isCreate => '#27ae60',
                                            $statusChange && in_array($newStatus, [\App\Models\Order::STATUS_DELIVERED]) => '#27ae60',
                                            $statusChange && in_array($newStatus, [\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::STATUS_RETURNED]) => '#e74c3c',
                                            $statusChange && in_array($newStatus, [\App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_COURIER_ENTRY, \App\Models\Order::STATUS_COURIER_PENDING]) => '#3498db',
                                            default => 'var(--primary)',
                                        };
                                    @endphp
                                    <div class="position-relative mb-3 ps-3" style="margin-left:0;">
                                        <span class="position-absolute" style="left:-9px;top:4px;width:16px;height:16px;border-radius:50%;background:{{ $dotColor }};border:2.5px solid #fff;box-shadow:0 0 0 2px {{ $dotColor }}33;display:block;"></span>
                                        <div class="fw-600" style="font-size:13.5px;color:var(--text-dark);">{{ $label }}</div>
                                        <div style="font-size:12px;color:var(--text-muted);">
                                            {{ $history->created_at?->format('d M Y, h:i A') }}
                                        </div>
                                        @if($statusChange && isset($history->changes['status']['from']))
                                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                                            {{ \App\Models\Order::STATUS_LIST[$history->changes['status']['from']] ?? '' }}
                                            <i class="fas fa-arrow-right mx-1" style="font-size:10px;"></i>
                                            {{ \App\Models\Order::STATUS_LIST[$history->changes['status']['to']] ?? '' }}
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Courier History --}}
                        <div class="col-lg-3">
                            <h6 class="fw-bold mb-3" style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;border-bottom:1.5px solid var(--border);padding-bottom:8px;">
                                <i class="fas fa-truck me-1"></i>Courier Info
                            </h6>

                            @if($order->courierLogs->isEmpty())
                                <p class="text-muted" style="font-size:13.5px;">Not yet dispatched to courier.</p>
                            @else
                                @php $firstCourierLog = $order->courierLogs->sortByDesc('created_at')->first(); @endphp
                                @if($firstCourierLog?->tracking_code)
                                <div class="mb-3">
                                    <a href="https://steadfast.com.bd/t/{{ $firstCourierLog->tracking_code }}" target="_blank"
                                       style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:#fff;background:var(--primary-gradient);padding:5px 14px;border-radius:20px;text-decoration:none;">
                                        <i class="fas fa-external-link-alt" style="font-size:10px;"></i> Show in Courier
                                    </a>
                                </div>
                                @endif
                                <div class="d-flex flex-column gap-3">
                                    @foreach($order->courierLogs->sortByDesc('created_at') as $log)
                                    <div class="p-3" style="background:#fafafa;border:1.5px solid var(--border);border-radius:10px;font-size:13px;">
                                        @if($log->consignment_id)
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="color:var(--text-muted);">Consignment</span>
                                            <span class="fw-600 font-monospace" style="font-size:12px;">{{ $log->consignment_id }}</span>
                                        </div>
                                        @endif
                                        @if($log->tracking_code)
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="color:var(--text-muted);">Tracking</span>
                                            <span class="fw-600 font-monospace" style="font-size:12px;">{{ $log->tracking_code }}</span>
                                        </div>
                                        @endif
                                        @if($log->status)
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="color:var(--text-muted);">Status</span>
                                            <span class="badge" style="background:var(--primary-gradient);font-size:11px;padding:3px 8px;">{{ ucwords(str_replace('_', ' ', $log->status)) }}</span>
                                        </div>
                                        @endif
                                        @if($log->tracking_message)
                                        <div class="mt-2 pt-2" style="border-top:1px solid var(--border);font-size:12.5px;color:var(--text-dark);">
                                            <i class="fas fa-comment-alt me-1" style="color:var(--primary);"></i>{{ $log->tracking_message }}
                                        </div>
                                        @endif
                                        <div class="mt-2" style="font-size:11.5px;color:var(--text-muted);">
                                            <i class="fas fa-clock me-1"></i>{{ $log->created_at?->format('d M Y, h:i A') }}
                                        </div>

                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
            @endforeach

        @endif

    @else

        {{-- Tips when not searched yet --}}
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="row g-3 text-center">
                    @php
                        $tips = [
                            ['icon' => 'fas fa-shopping-bag', 'title' => 'Order Number', 'text' => 'Find your 4–6 digit order number in the confirmation message we sent after your order was placed.'],
                            ['icon' => 'fas fa-mobile-alt',   'title' => 'Phone Number', 'text' => 'Enter the phone number you used when placing the order to see all associated orders.'],
                            ['icon' => 'fas fa-shield-alt',   'title' => 'Privacy Protected', 'text' => 'Your address and phone number are partially hidden to keep your information secure.'],
                        ];
                    @endphp
                    @foreach($tips as $tip)
                    <div class="col-md-4">
                        <div class="card border-0 h-100 p-2" style="border-radius:var(--radius);box-shadow:var(--shadow);">
                            <div class="card-body text-center p-4">
                                <div style="width:52px;height:52px;background:linear-gradient(135deg,#dcf5ea,#c3ecd9);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                                    <i class="{{ $tip['icon'] }}" style="font-size:20px;color:var(--primary);"></i>
                                </div>
                                <h6 class="fw-bold mb-2" style="font-size:14px;color:var(--text-dark);">{{ $tip['title'] }}</h6>
                                <p class="mb-0" style="font-size:13px;color:var(--text-muted);line-height:1.7;">{{ $tip['text'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    @endif

</div>
</section>
@endsection
