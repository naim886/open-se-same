@php
    $adminUser = auth()->user();
    $canAny = static fn (array $permissions): bool => (bool) collect($permissions)->contains(
        static fn (string $permission) => $adminUser?->can($permission)
    );
@endphp
<nav class="sb-sidenav accordion theme-bg shadow-right" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            @if($adminUser?->can('dashboard.view'))
                <div class="sb-sidenav-menu-heading">Core</div>
                <a class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}" href="{{ route('dashboard.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
            @endif

            @if($canAny(['orders.view', 'orders.create', 'customers.view']))
                <div class="sb-sidenav-menu-heading">Sales</div>

                @if($adminUser?->can('orders.view'))
                    <!-- Orders (POS lives on its own link below) -->
                    <a class="nav-link {{ request()->routeIs('orders.*') && !request()->routeIs('orders.create') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                        Orders
                    </a>
                @endif

                @if($adminUser?->can('orders.create'))
                    <!-- POS -->
                    <a class="nav-link {{ request()->routeIs('orders.create') ? 'active' : '' }}" href="{{ route('orders.create') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-cash-register"></i></div>
                        POS
                    </a>
                @endif

                @if($adminUser?->can('customers.view'))
                    <!-- Customers -->
                    <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                        Customers
                    </a>
                @endif
            @endif

            @if($canAny(['products.view', 'categories.view', 'brands.view', 'specifications.view', 'attributes.view']))
                <div class="sb-sidenav-menu-heading">Catalog</div>

                @if($adminUser?->can('products.view'))
                    <!-- Products -->
                    <a class="nav-link {{ request()->routeIs('product.*') ? 'active' : '' }}" href="{{ route('product.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                        Products
                    </a>
                @endif

                @if($adminUser?->can('categories.view'))
                    <!-- Categories -->
                    <a class="nav-link {{ request()->routeIs('category.*') ? 'active' : '' }}" href="{{ route('category.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                        Categories
                    </a>
                @endif

                @if($adminUser?->can('brands.view'))
                    <!-- Brands -->
                    <a class="nav-link {{ request()->routeIs('brand.*') ? 'active' : '' }}" href="{{ route('brand.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-copyright"></i></div>
                        Brands
                    </a>
                @endif

                @if($adminUser?->can('specifications.view'))
                    <!-- Specifications -->
                    <a class="nav-link {{ request()->routeIs('specification.*') ? 'active' : '' }}" href="{{ route('specification.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-list-alt"></i></div>
                        Specifications
                    </a>
                @endif

                @if($adminUser?->can('attributes.view'))
                    <!-- Variation attributes -->
                    <a class="nav-link {{ request()->routeIs('attribute-name.*') ? 'active' : '' }}" href="{{ route('attribute-name.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-font"></i></div>
                        Attribute Names
                    </a>

                    <a class="nav-link {{ request()->routeIs('attribute-value.*') ? 'active' : '' }}" href="{{ route('attribute-value.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-align-left"></i></div>
                        Attribute Values
                    </a>
                @endif
            @endif

            @if($canAny(['banners.view', 'flash_sales.view', 'top_selling.view', 'coupons.view']))
                <div class="sb-sidenav-menu-heading">Marketing</div>

                @if($adminUser?->can('banners.view'))
                    <!-- Banner Management -->
                    <a class="nav-link {{ request()->routeIs('banner.*') ? 'active' : '' }}" href="{{ route('banner.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-image"></i></div>
                        Banners
                    </a>
                @endif

                @if($adminUser?->can('flash_sales.view'))
                    <!-- Flash Sales -->
                    <a class="nav-link {{ request()->routeIs('flash-sale.*') ? 'active' : '' }}" href="{{ route('flash-sale.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-bolt"></i></div>
                        Flash Sales
                    </a>
                @endif

                @if($adminUser?->can('top_selling.view'))
                    <!-- Top Selling Products -->
                    <a class="nav-link {{ request()->routeIs('top-selling.*') ? 'active' : '' }}" href="{{ route('top-selling.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-fire"></i></div>
                        Top Selling
                    </a>
                @endif

                @if($adminUser?->can('coupons.view'))
                    <!-- Coupons -->
                    <a class="nav-link {{ request()->routeIs('coupon.*') ? 'active' : '' }}" href="{{ route('coupon.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-ticket"></i></div>
                        Coupons
                    </a>
                @endif

                {{-- Popup Leads --}}
                {{-- <a class="nav-link {{ request()->routeIs('popup-leads.*') ? 'active' : '' }}" href="{{ route('popup-leads.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                    Popup Leads
                </a> --}}
            @endif

            @if($canAny(['homepage.view', 'site_content.view', 'pages.view', 'blogs.view', 'contact.view']))
                <div class="sb-sidenav-menu-heading">Storefront Content</div>

                @if($adminUser?->can('homepage.view'))
                    <!-- Homepage Settings -->
                    <a class="nav-link {{ request()->routeIs('homepage-settings.*') ? 'active' : '' }}" href="{{ route('homepage-settings.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-th-large"></i></div>
                        Homepage Settings
                    </a>
                @endif

                @if($adminUser?->can('site_content.view'))
                    <!-- Site Contents -->
                    <a class="nav-link {{ request()->routeIs('site-content.*') ? 'active' : '' }}" href="{{ route('site-content.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                        Site Content
                    </a>
                @endif

                @if($adminUser?->can('pages.view'))
                    <!-- Pages -->
                    <a class="nav-link {{ request()->routeIs('page.*') ? 'active' : '' }}" href="{{ route('page.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                        Pages
                    </a>
                @endif

                @if($adminUser?->can('blogs.view'))
                    <!-- Blogs -->
                    <a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}" href="{{ route('blog.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-blog"></i></div>
                        Blogs
                    </a>
                @endif

                @if($adminUser?->can('contact.view'))
                    <!-- Contact Messages -->
                    <a class="nav-link {{ request()->routeIs('contact.*') ? 'active' : '' }}" href="{{ route('contact.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                        Contact Messages
                        @php $unread = \App\Models\ContactMessage::unreadCount(); @endphp
                        @if($unread > 0)
                            <span class="badge bg-warning text-dark ms-auto" style="font-size:11px;">{{ $unread }}</span>
                        @endif
                    </a>
                @endif
            @endif

            @if($canAny([
                'reports.sales', 'reports.summary', 'reports.categories', 'reports.products',
                'reports.margin', 'reports.stock', 'reports.commission', 'reports.balance',
            ]))
                <div class="sb-sidenav-menu-heading">Reports</div>

                @if($adminUser?->can('reports.sales'))
                    <!-- Sales Report -->
                    <a class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}" href="{{ route('reports.sales') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                        Sales Report
                    </a>
                @endif

                @if($adminUser?->can('reports.summary'))
                    <!-- Sales Summary -->
                    <a class="nav-link {{ request()->routeIs('reports.sales-summary') ? 'active' : '' }}" href="{{ route('reports.sales-summary') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-column"></i></div>
                        Sales Summary
                    </a>
                @endif

                @if($adminUser?->can('reports.categories'))
                    <!-- Category Sales -->
                    <a class="nav-link {{ request()->routeIs('reports.category-sales') ? 'active' : '' }}" href="{{ route('reports.category-sales') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                        Category Sales
                    </a>
                @endif

                @if($adminUser?->can('reports.products'))
                    <!-- Product Performance -->
                    <a class="nav-link {{ request()->routeIs('reports.product-performance') ? 'active' : '' }}" href="{{ route('reports.product-performance') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-trophy"></i></div>
                        Product Performance
                    </a>
                @endif

                @if($adminUser?->can('reports.margin'))
                    <!-- Profit & Margin -->
                    <a class="nav-link {{ request()->routeIs('reports.margin') ? 'active' : '' }}" href="{{ route('reports.margin') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-coins"></i></div>
                        Profit &amp; Margin
                    </a>
                @endif

                @if($adminUser?->can('reports.stock'))
                    <!-- Stock Value -->
                    <a class="nav-link {{ request()->routeIs('reports.stock-value') ? 'active' : '' }}" href="{{ route('reports.stock-value') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-warehouse"></i></div>
                        Stock Value
                    </a>
                @endif

                {{-- Commission Report --}}
                @if($adminUser?->can('reports.commission'))
                    <a class="nav-link {{ request()->routeIs('reports.commission') ? 'active' : '' }}" href="{{ route('reports.commission') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-hand-holding-usd"></i></div>
                        Commission
                    </a>
                @endif

                {{-- Balance Sheet --}}
                @if($adminUser?->can('reports.balance'))
                    <a class="nav-link {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}" href="{{ route('reports.balance-sheet') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>
                        Balance Sheet
                    </a>
                @endif
            @endif

            @if($canAny(['users.view', 'roles.view', 'settings.view']))
                <div class="sb-sidenav-menu-heading">Administration</div>

                @if($adminUser?->can('users.view'))
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>
                        Users
                    </a>
                @endif

                @if($adminUser?->can('roles.view'))
                    <a class="nav-link {{ request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
                        Roles &amp; Permissions
                    </a>
                @endif

                @if($adminUser?->can('settings.view'))
                    <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-sliders-h"></i></div>
                        Settings
                    </a>
                @endif
            @endif

            {{-- Accounts --}}
            {{-- <a class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="{{ route('accounts.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                Accounts
            </a> --}}

            {{-- Expenses --}}
            {{-- <a class="nav-link {{ request()->routeIs('expense.*') ? 'active' : '' }}" href="{{ route('expense.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div>
                Expenses
            </a> --}}
        </div>
    </div>
    <div class="sb-sidenav-footer">
        <div class="small">Logged in as:</div>
        {{ $adminUser?->name }}
    </div>
</nav>
