@php
    $metaPixelId = \App\Models\Setting::effective('analytics.meta.pixel_id', '2238997253621126');
    $ga4Id       = \App\Models\Setting::effective('analytics.ga4.measurement_id', 'G-0C0K5XZEQF');
    $gaId        = \App\Models\Setting::effective('analytics.ga.measurement_id', '');

    if (!session()->has('capi_uid')) {
        session(['capi_uid' => (string) \Illuminate\Support\Str::uuid()]);
    }

    $pixelExternalId = hash('sha256', (string) session('capi_uid'));

    if (auth()->check() && auth()->user()->phone) {
        $pixelPhone = preg_replace('/\D/', '', (string) auth()->user()->phone);

        if (strlen($pixelPhone) === 11 && str_starts_with($pixelPhone, '0')) {
            $pixelPhone = '880' . substr($pixelPhone, 1);
        }

        if ($pixelPhone !== '') {
            $pixelExternalId = hash('sha256', $pixelPhone);
        }
    }
    $pixelIp = trim(explode(',', request()->header('CF-Connecting-IP') ?? request()->header('X-Forwarded-For') ?? request()->ip())[0]);
    $pixelUa = request()->userAgent() ?? '';
@endphp

@if($metaPixelId)
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', {!! json_encode($metaPixelId) !!}, {
        client_ip_address: {!! json_encode($pixelIp) !!},
        client_user_agent: {!! json_encode($pixelUa) !!},
        external_id: {!! json_encode($pixelExternalId) !!}
    });
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
@endif

@if($ga4Id || $gaId)
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id ?: $gaId }}"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    @if($ga4Id)
    gtag('config', {!! json_encode($ga4Id) !!});
    @endif
    @if($gaId)
    gtag('config', {!! json_encode($gaId) !!});
    @endif
    </script>
    <!-- End Google Analytics -->
@endif

<script>
    window.ocMetaPixelId = {!! json_encode($metaPixelId) !!};

    /**
     * Send one ecommerce event to Meta Pixel + Google Analytics/GA4.
     *
     * @param {string} metaEvent  Meta standard event name (ViewContent, AddToCart, ...)
     * @param {object} params     Meta parameters (value, currency, content_ids, content_name, ...)
     * @param {string} eventId    Shared Meta event ID for browser/server deduplication
     * @param {object} gaExtra    Optional GA4 overrides: event, items, transaction_id, coupon, shipping, tax
     */
    window.ocTrack = function (metaEvent, params, eventId, gaExtra) {
        params = params || {};
        gaExtra = gaExtra || {};

        if (typeof fbq === 'function' && metaEvent) {
            if (eventId) {
                fbq('track', metaEvent, params, { eventID: String(eventId) });
            } else {
                fbq('track', metaEvent, params);
            }
        }

        if (typeof gtag !== 'function') {
            return;
        }

        var gaMap = {
            ViewContent: 'view_item',
            AddToCart: 'add_to_cart',
            InitiateCheckout: 'begin_checkout',
            AddPaymentInfo: 'add_payment_info',
            Purchase: 'purchase',
            Search: 'search',
            Lead: 'generate_lead',
            Contact: 'contact'
        };

        var gaName = gaExtra.event || gaMap[metaEvent] || String(metaEvent || '').toLowerCase();
        var items = gaExtra.items;

        if (!items) {
            var ids = Array.isArray(params.content_ids) ? params.content_ids : [];
            var quantity = Number(params.num_items || 1);
            var value = Number(params.value || 0);
            var price = ids.length === 1 ? value / Math.max(1, quantity) : 0;

            items = ids.map(function (id) {
                return {
                    item_id: String(id),
                    item_name: params.content_name || '',
                    price: price,
                    quantity: quantity
                };
            });
        }

        var gaParams = {
            currency: params.currency || 'BDT',
            value: Number(params.value || 0),
            items: items
        };

        if (params.order_id) {
            gaParams.transaction_id = String(params.order_id);
        }
        if (gaExtra.transaction_id) {
            gaParams.transaction_id = String(gaExtra.transaction_id);
        }
        if (params.search_string) {
            gaParams.search_term = String(params.search_string);
        }
        if (gaExtra.coupon) {
            gaParams.coupon = gaExtra.coupon;
        }
        if (gaExtra.shipping !== undefined) {
            gaParams.shipping = Number(gaExtra.shipping);
        }
        if (gaExtra.tax !== undefined) {
            gaParams.tax = Number(gaExtra.tax);
        }

        gtag('event', gaName, gaParams);
    };

    window.ocTrackGA4 = function (eventName, params) {
        if (typeof gtag === 'function' && eventName) {
            gtag('event', eventName, params || {});
        }
    };
</script>
