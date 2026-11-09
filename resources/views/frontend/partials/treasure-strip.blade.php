{{-- "Unlock Hidden Treasures" icon strip, rendered directly below the homepage H1. --}}
@if(!empty($block) && !empty($block['items']))
    <style>
        @media (max-width: 767.98px) {
            .treasure-strip {
                gap: 10px !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
            }
            .treasure-item {
                width: calc(33.333% - 7px) !important;
                padding: 10px 6px !important;
                border-radius: 12px !important;
            }
            .treasure-icon {
                width: 64px !important;
                height: 64px !important;
                padding: 8px !important;
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
