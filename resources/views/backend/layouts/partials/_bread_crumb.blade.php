<div class="brad-crumb mt-4">
    <div class="row align-items-center">
        <div class="col-lg-6 text-lg-start text-center">
            <nav
                style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center justify-content-lg-start">
                    <li class="breadcrumb-item"><a href="{{route('dashboard.index')}}">Dashboard</a></li>
                    @isset($cms_content['module'])
                        <li class="breadcrumb-item" aria-current="page"><a
                                href="{{$cms_content['module_url'] ?? null}}">{{$cms_content['module']}}</a></li>
                    @endisset
                    @isset($cms_content['sub_module'])
                        <li class="breadcrumb-item active" aria-current="page">{{$cms_content['sub_module']}}</li>
                    @endisset
                </ol>
            </nav>
        </div>
        <div class="col-lg-6 text-lg-end text-center mt-2 mt-lg-0">
            @isset($cms_content['button_title'] , $cms_content['button_url'], $cms_content['type'])
                <a href="{{$cms_content['button_url']}}">
                    <button class="btn btn-sm theme-button-outline" type="submit">
                        @if($cms_content['type'] == 'list')
                            <i class="fa-solid fa-list"></i>
                        @else
                            <i class="fa-solid fa-plus"></i>
                        @endif
                        {{$cms_content['button_title']}}</button>
                </a>
            @endisset
        </div>
    </div>
</div>

