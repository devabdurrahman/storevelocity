@php
    $pixelId = $store->facebook_pixel_id;
    $gaId = $store->ga_measurement_id;
@endphp

{{-- Meta (Facebook) Pixel base code. Only the store's own ID is inserted, JSON-escaped. --}}
@if ($pixelId)
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', @js($pixelId));
        fbq('track', 'PageView');
    </script>
@endif

{{-- Google Analytics 4 --}}
@if ($gaId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gaId) }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @js($gaId));
    </script>
@endif

{{-- Translates our one payload into each tool's events. Safe to call when neither is enabled. --}}
<script>
    window.storeTrack = function (name, d) {
        try {
            var items = d.items || [];
            if (!items.length) { return; }

            var ids = items.map(function (i) { return String(i.id); });
            var contents = items.map(function (i) { return { id: String(i.id), quantity: i.quantity }; });
            var count = items.reduce(function (n, i) { return n + i.quantity; }, 0);

            if (window.fbq) {
                var fb = { content_ids: ids, content_type: 'product', contents: contents, currency: d.currency };

                if (name === 'view_item') {
                    fbq('track', 'ViewContent', {
                        content_ids: ids, content_type: 'product',
                        content_name: items[0].name, value: d.value, currency: d.currency
                    });
                } else if (name === 'add_to_cart') {
                    fbq('track', 'AddToCart', Object.assign({ value: d.value }, fb));
                } else if (name === 'begin_checkout') {
                    fbq('track', 'InitiateCheckout', Object.assign({ value: d.value, num_items: count }, fb));
                } else if (name === 'purchase') {
                    fbq('track', 'Purchase', Object.assign({ value: d.total, num_items: count }, fb), { eventID: d.transaction_id });
                }
            }

            if (window.gtag) {
                var ga = {
                    currency: d.currency,
                    value: d.value,
                    items: items.map(function (i) {
                        return {
                            item_id: String(i.id), item_name: i.name, price: i.price,
                            quantity: i.quantity, item_category: i.category || undefined
                        };
                    })
                };

                if (name === 'purchase') {
                    ga.transaction_id = d.transaction_id;
                    ga.shipping = d.shipping;
                }

                gtag('event', name, ga);
            }
        } catch (e) { /* tracking must never break the store */ }
    };
</script>