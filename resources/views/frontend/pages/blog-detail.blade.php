@extends('frontend.layouts.app')

@section('title',          $blog->meta_title ?? $blog->title . ' - Open Se Same')
@if($blog->meta_description)
@section('meta_description', $blog->meta_description)
@endif
@section('og_title',       $blog->meta_title ?? $blog->title . ' - Open Se Same')
@section('og_description', $blog->meta_description ?? $blog->excerpt ?? '')
@section('og_type',        'article')
@section('og_image',       $blog->thumbnail ? asset('storage/'.$blog->thumbnail) : asset('images/og.webp'))

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
use Illuminate\Support\Str;
$articleSchema = Schema::blogPosting()
    ->headline($blog->meta_title ?? $blog->title)
    ->name($blog->meta_title ?? $blog->title)
    ->description(strip_tags($blog->meta_description ?? Str::limit(strip_tags($blog->description ?? ''), 200)))
    ->image($blog->cover_photo ? asset('storage/'.$blog->cover_photo) : asset('images/og.webp'))
    ->url(route('shop.blog', $blog->slug))
    ->datePublished($blog->created_at->toIso8601String())
    ->dateModified($blog->updated_at->toIso8601String())
    ->author(
        $blog->author
            ? Schema::person()->name($blog->author)
            : Schema::organization()->name('Open Se Same')
    )
    ->publisher(
        Schema::organization()
            ->name('Open Se Same')
            ->logo(Schema::imageObject()->url(asset('images/logo-web.png')))
    );
echo $articleSchema->toScript();
echo Schema::breadcrumbList()->itemListElement([
    Schema::listItem()->position(1)->name('Home')->item(route('shop.home')),
    Schema::listItem()->position(2)->name('Blog')->item(route('shop.blogs')),
    Schema::listItem()->position(3)->name(Str::limit($blog->title, 60))->item(route('shop.blog', $blog->slug)),
])->toScript();
if ($blog->faqs && $blog->faqs->count() > 0) {
    echo Schema::fAQPage()
        ->mainEntity(
            $blog->faqs->map(fn($faq) =>
                Schema::question()
                    ->name($faq->question)
                    ->acceptedAnswer(Schema::answer()->text(strip_tags($faq->answer)))
            )->toArray()
        )
        ->toScript();
}
@endphp
@endsection

@section('content')

<!-- Breadcrumb -->
<div class="shop-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shop.blogs') }}">Blog</a></li>
                <li class="breadcrumb-item active text-truncate" style="max-width:220px;">{{ $blog->title }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-5" style="background:var(--bg-light);">
    <div class="container">
        <div class="row g-4 align-items-start">

            {{-- ===================== LEFT: Main Content (8 col) ===================== --}}
            <div class="col-lg-8">
                <article style="background:#fff; border-radius:16px; overflow:hidden; border:1px solid var(--border); box-shadow:var(--shadow);">

                    <!-- Cover Image -->
                    @if($blog->cover_photo)
                        <img src="{{ asset('storage/' . $blog->cover_photo) }}" alt="{{ $blog->title }}"
                             style="width:100%; max-height:400px; object-fit:cover; display:block;">
                    @endif

                    <div class="p-4 p-lg-5">

                        <!-- Title -->
                        <h1 class="fw-bold mb-3" style="color:var(--text-dark); font-size:24px; line-height:1.4;">
                            {{ $blog->title }}
                        </h1>

                        <!-- Meta Bar -->
                        <div class="d-flex align-items-center flex-wrap gap-3 mb-4 pb-4"
                             style="border-bottom:2px solid var(--border); font-size:13px; color:var(--text-muted);">
                            @if($blog->author)
                                <span style="display:inline-flex;align-items:center;gap:6px;">
                                    <span style="width:26px;height:26px;background:var(--primary-gradient);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-user" style="font-size:10px;color:#fff;"></i>
                                    </span>
                                    <strong style="color:var(--text-dark);">{{ $blog->author }}</strong>
                                </span>
                            @endif
                            <span><i class="fas fa-calendar-alt me-1" style="color:var(--primary);"></i>{{ $blog->created_at->format('d M, Y') }}</span>
                            <span><i class="fas fa-clock me-1" style="color:var(--primary);"></i>{{ $blog->read_time }} min read</span>
                            <span><i class="fas fa-eye me-1" style="color:var(--primary);"></i>{{ number_format($blog->view_count) }} views</span>
                            @if($blog->faqs->isNotEmpty())
                                <span><i class="fas fa-question-circle me-1" style="color:var(--primary);"></i>{{ $blog->faqs->count() }} FAQ(s)</span>
                            @endif
                        </div>

                        <!-- Content -->
                        @if($blog->description)
                            <div class="blog-content" style="font-size:15px; line-height:1.9; color:var(--text-body);">
                                {!! $blog->description !!}
                            </div>
                        @endif

                        <!-- Back Button -->
                        <div class="mt-5 pt-3" style="border-top:1px solid var(--border);">
                            <a href="{{ route('shop.blogs') }}" style="
                                display:inline-flex; align-items:center; gap:8px;
                                padding:11px 24px; border:2px solid var(--primary);
                                border-radius:8px; color:var(--primary);
                                font-size:14px; font-weight:600; text-decoration:none;
                                transition:var(--transition);"
                               onmouseover="this.style.background='var(--primary)';this.style.color='#fff';"
                               onmouseout="this.style.background='transparent';this.style.color='var(--primary)';">
                                <i class="fas fa-arrow-left"></i> Back to Blog
                            </a>
                        </div>
                    </div>
                </article>
            </div>

            {{-- ===================== RIGHT: Sidebar (4 col) ===================== --}}
            <div class="col-lg-4" style="position:sticky; top:24px;">

                {{-- FAQs Block --}}
                @if($blog->faqs->isNotEmpty())
                    <div class="mb-4" style="
                        background:#fff; border-radius:16px; overflow:hidden;
                        border:1px solid var(--border); box-shadow:var(--shadow);
                    ">
                        <div style="background:var(--primary-gradient); padding:16px 20px;">
                            <h6 class="mb-0 fw-bold" style="color:#fff; font-size:15px;">
                                <i class="fas fa-question-circle me-2"></i>Frequently Asked Questions
                                <span style="background:rgba(255,255,255,0.2); font-size:11px; padding:2px 8px; border-radius:20px; margin-left:6px;">{{ $blog->faqs->count() }}</span>
                            </h6>
                        </div>
                        <div class="accordion accordion-flush" id="sidebarFaqAccordion">
                            @foreach($blog->faqs as $faq)
                                <div class="accordion-item" style="border-bottom:1px solid var(--border);">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#sfaq{{ $faq->id }}"
                                                style="
                                                    font-size:13px; font-weight:600;
                                                    color:var(--text-dark); background:#fff;
                                                    padding:14px 16px; line-height:1.4;
                                                    box-shadow:none;
                                                ">
                                            {{ $faq->question }}
                                        </button>
                                    </h2>
                                    <div id="sfaq{{ $faq->id }}"
                                         class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                         data-bs-parent="#sidebarFaqAccordion">
                                        <div class="accordion-body" style="
                                            font-size:13px; line-height:1.7;
                                            color:var(--text-body); background:var(--bg-light);
                                            padding:12px 16px;
                                        ">{{ $faq->answer }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Related Blogs Block --}}
                @if($relatedBlogs->isNotEmpty())
                    <div style="background:#fff; border-radius:16px; overflow:hidden; border:1px solid var(--border); box-shadow:var(--shadow);">
                        <div style="background:var(--primary-gradient); padding:16px 20px;">
                            <h6 class="mb-0 fw-bold" style="color:#fff; font-size:15px;">
                                <i class="fas fa-newspaper me-2"></i>More from Our Blog
                            </h6>
                        </div>
                        <div style="padding:8px 0;">
                            @foreach($relatedBlogs as $related)
                                <a href="{{ route('shop.blog', $related->slug) }}" class="text-decoration-none"
                                   style="display:flex; align-items:center; gap:12px; padding:12px 16px; transition:var(--transition); border-bottom:{{ !$loop->last ? '1px solid var(--border)' : 'none' }};"
                                   onmouseover="this.style.background='var(--bg-light)'"
                                   onmouseout="this.style.background='transparent'">
                                    @if($related->cover_photo)
                                        <img src="{{ asset('storage/' . $related->cover_photo) }}" alt="{{ $related->title }}"
                                             style="width:60px; height:60px; object-fit:cover; border-radius:8px; flex-shrink:0;">
                                    @else
                                        <div style="width:60px;height:60px;background:var(--primary-gradient);border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-feather-alt" style="color:rgba(255,255,255,0.7);font-size:16px;"></i>
                                        </div>
                                    @endif
                                    <div style="flex:1; min-width:0;">
                                        <p class="mb-1 fw-semibold" style="font-size:13px; color:var(--text-dark); line-height:1.35; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
                                            {{ $related->title }}
                                        </p>
                                        <p class="mb-0" style="font-size:11px; color:var(--text-muted);">
                                            <i class="fas fa-clock me-1"></i>{{ $related->read_time }} min
                                            &nbsp;·&nbsp;
                                            <i class="fas fa-eye me-1"></i>{{ number_format($related->view_count) }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>{{-- end sidebar --}}

        </div>{{-- end row --}}
    </div>
</section>

@endsection
