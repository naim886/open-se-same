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
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"
    /></noscript>
@endif

<script>
(function () {
    window.ocMetaPixelId = {!! json_encode($metaPixelId) !!};
    window.ocGa4Id = {!! json_encode($ga4Id) !!};
    window.ocGaId = {!! json_encode($gaId) !!};
    window.ocGaConfigs = [window.ocGa4Id, window.ocGaId].filter(Boolean);

    window.ocMetaQueue = window.ocMetaQueue || [];
    window.ocGaQueue = window.ocGaQueue || [];
    window.ocAnalyticsLoaded = false;
    window.ocAnalyticsLoading = false;

    var pixelOptions = {
        client_ip_address: {!! json_encode($pixelIp) !!},
        client_user_agent: {!! json_encode($pixelUa) !!},
        external_id: {!! json_encode($pixelExternalId) !!}
    };

    function sendMeta(metaEvent, params, eventId) {
        if (typeof window.fbq !== 'function') {
            return false;
        }

        if (eventId) {
            window.fbq('track', metaEvent, params, { eventID: String(eventId) });
        } else {
            window.fbq('track', metaEvent, params);
        }

        return true;
    }

    window.ocSendGa = function (metaEvent, params, gaExtra) {
        if (typeof window.gtag !== 'function') {
            return false;
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

        window.gtag('event', gaName, gaParams);

        return true;
    };

    window.ocTrack = function (metaEvent, params, eventId, gaExtra) {
        params = params || {};
        gaExtra = gaExtra || {};

        if (!sendMeta(metaEvent, params, eventId)) {
            window.ocMetaQueue.push([metaEvent, params, eventId]);
        }

        if (!window.ocSendGa(metaEvent, params, gaExtra)) {
            window.ocGaQueue.push([metaEvent, params, gaExtra]);
        }
    };

    window.ocTrackGA4 = function (eventName, params) {
        if (typeof window.gtag === 'function') {
            window.gtag('event', eventName, params || {});
        } else {
            window.ocGaQueue.push(['__raw', eventName, params || {}]);
        }
    };

    function flushQueues() {
        window.ocMetaQueue.splice(0).forEach(function (item) {
            sendMeta(item[0], item[1], item[2]);
        });

        window.ocGaQueue.splice(0).forEach(function (item) {
            if (item[0] === '__raw') {
                if (typeof window.gtag === 'function') {
                    window.gtag('event', item[1], item[2]);
                }
            } else {
                window.ocSendGa(item[0], item[1], item[2]);
            }
        });
    }

    function loadMetaPixel() {
        if (!window.ocMetaPixelId || typeof window.fbq === 'function') {
            return;
        }

        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');

        window.fbq('init', window.ocMetaPixelId, pixelOptions);
        window.fbq('track', 'PageView');
    }

    function loadGa() {
        if (!window.ocGaConfigs.length || typeof window.gtag === 'function') {
            return;
        }

        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());

        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(window.ocGaConfigs[0]);
        document.head.appendChild(script);

        window.ocGaConfigs.forEach(function (id) {
            window.gtag('config', id);
        });
    }

    function loadClarity() {
        if (window.__ocClarityLoaded) {
            return;
        }

        window.__ocClarityLoaded = true;
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "vqji8l47ab");
    }

    window.ocLoadAnalytics = function () {
        if (window.ocAnalyticsLoaded || window.ocAnalyticsLoading) {
            return;
        }

        window.ocAnalyticsLoading = true;

        loadMetaPixel();
        loadGa();
        loadClarity();

        window.ocAnalyticsLoaded = true;
        flushQueues();
    };

    // Load third-party tags after the first interaction, or 6 seconds after
    // load, so they never block the initial render or inflate TBT.
    ['pointerdown', 'keydown', 'scroll', 'touchstart'].forEach(function (eventName) {
        window.addEventListener(eventName, window.ocLoadAnalytics, { once: true, passive: true });
    });

    window.addEventListener('load', function () {
        window.setTimeout(window.ocLoadAnalytics, 6000);
    });
})();
</script>
