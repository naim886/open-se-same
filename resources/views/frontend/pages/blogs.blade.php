@extends('frontend.layouts.app')

@section('title', 'Blog - Open Se Same')
@section('meta_description', 'Read the latest tips, guides & stories from Open Se Same. Learn about preserved roses, lingerie care & fashion trends in Bangladesh.')
@section('og_title', 'Blog - Open Se Same')
@section('og_description', 'Read the latest tips, guides & stories from Open Se Same. Learn about preserved roses, lingerie care & fashion trends in Bangladesh.')
@section('robots', 'index, follow')

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
echo Schema::collectionPage()
    ->name('Blog - Open Se Same')
    ->description('Read the latest tips, guides & stories from Open Se Same. Learn about preserved roses, lingerie care & fashion trends in Bangladesh.')
    ->url(route('shop.blogs'))
    ->toScript();
echo Schema::breadcrumbList()->itemListElement([
    Schema::listItem()->position(1)->name('Home')->item(route('shop.home')),
    Schema::listItem()->position(2)->name('Blog')->item(route('shop.blogs')),
])->toScript();
@endphp
@endsection

@section('content')

<!-- Hero Banner -->
<div style="background:var(--primary-gradient); padding:52px 0 44px; text-align:center; position:relative; overflow:hidden;">
    <div style="position:absolute;inset:0;background:url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 20%22><path d=%22M0,10 Q25,20 50,10 Q75,0 100,10 L100,20 L0,20Z%22 fill=%22rgba(255,255,255,0.04)%22/></svg>') bottom/100% auto no-repeat;"></div>
    <div class="container" style="position:relative;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb justify-content-center" style="margin:0; padding:0; background:transparent;">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}" style="color:rgba(255,255,255,0.75); text-decoration:none;">Home</a></li>
                <li class="breadcrumb-item active" style="color:#fff;">Blog</li>
            </ol>
        </nav>
        <h1 class="fw-bold mb-2" style="color:#fff; font-size:32px; letter-spacing:-0.5px;">Our Blog</h1>
        <p style="color:rgba(255,255,255,0.78); font-size:15px; margin-bottom:28px;">Tips, guides &amp; stories from Open Se Same</p>
        <!-- Search Box -->
        <form method="GET" action="{{ route('shop.blogs') }}" style="max-width:500px; margin:0 auto;">
            <div style="display:flex; background:#fff; border-radius:50px; box-shadow:0 4px 20px rgba(0,0,0,0.18); overflow:hidden;">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search articles, topics..."
                       style="flex:1; border:none; outline:none; padding:14px 22px; font-size:14px; color:var(--text-dark); background:transparent;">
                <button type="submit"
                        style="background:var(--primary-gradient); border:none; padding:0 26px; color:#fff; font-size:16px; cursor:pointer; border-radius:0 50px 50px 0; transition:var(--transition);"
                        onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<section class="py-5" style="background:var(--bg-light);">
    <div class="container">

        @if($search)
            <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">
                <span style="font-size:14px; color:var(--text-muted);">
                    Results for: <strong style="color:var(--primary-dark);">"{{ $search }}"</strong>
                    — {{ $blogs->total() }} post(s) found
                </span>
                <a href="{{ route('shop.blogs') }}" style="font-size:13px; color:var(--primary); text-decoration:none;">
                    <i class="fas fa-times-circle me-1"></i>Clear
                </a>
            </div>
        @endif

        @if($blogs->isEmpty())
            <div class="text-center py-5">
                <div style="width:80px;height:80px;background:var(--primary-gradient);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <i class="fas fa-search fa-2x" style="color:#fff;"></i>
                </div>
                <h5 class="fw-bold mb-2" style="color:var(--text-dark);">No blog posts found.</h5>
                <p style="color:var(--text-muted);">{{ $search ? 'Try a different keyword.' : 'Check back soon!' }}</p>
                @if($search)
                    <a href="{{ route('shop.blogs') }}" style="
                        display:inline-block;margin-top:14px;padding:10px 26px;
                        background:var(--primary-gradient);color:#fff;border-radius:8px;
                        font-size:14px;font-weight:600;text-decoration:none;">
                        View All Posts
                    </a>
                @endif
            </div>
        @else
            <div class="row g-4">
                @foreach($blogs as $blog)
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('shop.blog', $blog->slug) }}" class="text-decoration-none d-block h-100">
                        <article class="h-100" style="
                            background:#fff;
                            border-radius:16px;
                            overflow:hidden;
                            box-shadow:0 2px 14px rgba(122,18,53,0.07);
                            transition:all 0.28s ease;
                            border:1px solid var(--border);
                            display:flex;
                            flex-direction:column;
                        "
                        onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 16px 40px rgba(122,18,53,0.16)';"
                        onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 14px rgba(122,18,53,0.07)';">

                            <!-- Thumbnail -->
                            <div style="position:relative; overflow:hidden;">
                                @if($blog->cover_photo)
                                    <img src="{{ asset('storage/' . $blog->cover_photo) }}" alt="{{ $blog->title }}"
                                         style="width:100%; height:210px; object-fit:cover; display:block; transition:transform 0.4s ease;"
                                         onmouseover="this.style.transform='scale(1.06)'"
                                         onmouseout="this.style.transform='scale(1)'">
                                @else
                                    <div style="width:100%; height:210px; background:var(--primary-gradient); display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-feather-alt fa-3x" style="color:rgba(255,255,255,0.55);"></i>
                                    </div>
                                @endif
                                <!-- Read-time badge -->
                                <div style="
                                    position:absolute; bottom:10px; left:12px;
                                    background:rgba(0,0,0,0.55); backdrop-filter:blur(4px);
                                    color:#fff; font-size:11px; font-weight:600;
                                    padding:4px 10px; border-radius:20px; letter-spacing:0.3px;
                                "><i class="fas fa-clock me-1"></i>{{ $blog->read_time }} min read</div>
                            </div>

                            <!-- Body -->
                            <div class="p-4" style="flex:1; display:flex; flex-direction:column;">

                                <!-- Meta row -->
                                <div class="d-flex align-items-center flex-wrap gap-2 mb-2" style="font-size:11.5px; color:var(--text-muted);">
                                    @if($blog->author)
                                        <span style="display:inline-flex;align-items:center;gap:5px;">
                                            <span style="width:20px;height:20px;background:var(--primary-gradient);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <i class="fas fa-user" style="font-size:8px;color:#fff;"></i>
                                            </span>
                                            {{ $blog->author }}
                                        </span>
                                        <span style="opacity:.4;">|</span>
                                    @endif
                                    <span><i class="fas fa-calendar-alt me-1"></i>{{ $blog->created_at->format('d M Y') }}</span>
                                    <span style="opacity:.4;">|</span>
                                    <span><i class="fas fa-eye me-1"></i>{{ number_format($blog->view_count) }}</span>
                                    @if($blog->faqs_count > 0)
                                        <span style="opacity:.4;">|</span>
                                        <span><i class="fas fa-question-circle me-1"></i>{{ $blog->faqs_count }} FAQ</span>
                                    @endif
                                </div>

                                <!-- Title -->
                                <h5 class="fw-bold mb-2" style="color:var(--text-dark); font-size:16px; line-height:1.45;">
                                    {{ $blog->title }}
                                </h5>

                                <!-- Excerpt -->
                                @if($blog->meta_description)
                                    <p style="font-size:13.5px; color:var(--text-body); line-height:1.65; margin-bottom:14px; opacity:0.82; flex:1;">
                                        {{ Str::limit($blog->meta_description, 105) }}
                                    </p>
                                @else
                                    <div style="flex:1;"></div>
                                @endif

                                <!-- Footer CTA -->
                                <div class="d-flex align-items-center justify-content-between mt-auto pt-3"
                                     style="border-top:1px solid var(--border);">
                                    <span style="font-size:13px; font-weight:700; color:var(--primary); display:flex; align-items:center; gap:6px;">
                                        Read Article
                                        <span style="
                                            width:22px;height:22px;background:var(--primary-gradient);border-radius:50%;
                                            display:inline-flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-arrow-right" style="font-size:9px;color:#fff;"></i>
                                        </span>
                                    </span>
                                    @if($blog->faqs_count > 0)
                                        <span style="
                                            background:rgba(194,67,107,0.09);color:var(--primary);
                                            font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;">
                                            {{ $blog->faqs_count }} FAQ{{ $blog->faqs_count > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    </a>
                </div>
                @endforeach
            </div>

            @if($blogs->hasPages())
                <div class="mt-5 d-flex justify-content-center">{{ $blogs->links() }}</div>
            @endif
        @endif
    </div>
</section>

@endsection
