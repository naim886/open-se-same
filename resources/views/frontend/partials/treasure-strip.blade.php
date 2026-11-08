{{-- "Unlock Hidden Treasures" icon strip, rendered directly below the homepage H1. --}}
@if(!empty($block) && !empty($block['items']))
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
