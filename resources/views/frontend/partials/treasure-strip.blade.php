{{-- "Unlock Hidden Treasures" icon strip, rendered directly below the homepage H1. --}}
@if(!empty($block) && !empty($block['items']))
    <style>
        @media (max-width: 767.98px) {
            .treasure-strip {
                gap: 6px !important;
                flex-wrap: nowrap !important;
                justify-content: space-between !important;
            }
            .treasure-item {
                flex: 1 1 0 !important;
                width: auto !important;
                min-width: 0 !important;
                padding: 8px 4px !important;
                border-radius: 12px !important;
            }
            .treasure-icon {
                width: clamp(40px, 13vw, 56px) !important;
                height: clamp(40px, 13vw, 56px) !important;
                padding: 6px !important;
            }
            .treasure-label {
                display: none !important;
            }
        }
    </style>
    <section class="treasure-section" aria-label="Unlock hidden treasures">
        <div class="container">
            @if(!empty($block['title']))
                <h2 class="treasure-title">{{ $block['title'] }}</h2>
            @endif
            <div class="treasure-strip">
                @foreach($block['items'] as $item)
                    <a href="{{ $item['link'] ?: route('shop.products') }}"
                       class="treasure-item"
                       aria-label="{{ $item['label'] }}">
                        <span class="treasure-icon">
                            <img src="{{ asset($item['icon']) }}" alt="{{ $item['label'] }}" loading="lazy">
                        </span>
                        <span class="treasure-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
