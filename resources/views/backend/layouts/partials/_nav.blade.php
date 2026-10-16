<nav class="sb-sidenav accordion theme-bg shadow-right" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <div class="sb-sidenav-menu-heading">Core</div>
            <a class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}" href="{{ route('dashboard.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                Dashboard
            </a>

            <div class="sb-sidenav-menu-heading">Sales Management</div>

            <!-- Order Management -->
            <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                Orders
            </a>

            <!-- POS -->
            <a class="nav-link {{ request()->routeIs('orders.create') ? 'active' : '' }}" href="{{ route('orders.create') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-cash-register"></i></div>
                POS
            </a>

            <!-- Customers -->
            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                Customers
            </a>

            <!-- Sales Report -->
            <a class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}" href="{{ route('reports.sales') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                Sales Report
            </a>

            {{-- Commission Report --}}
            {{-- <a class="nav-link {{ request()->routeIs('reports.commission') ? 'active' : '' }}" href="{{ route('reports.commission') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-hand-holding-usd"></i></div>
                Commission
            </a> --}}

            <!-- Product Performance -->
            <a class="nav-link {{ request()->routeIs('reports.product-performance') ? 'active' : '' }}" href="{{ route('reports.product-performance') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-trophy"></i></div>
                Product Performance
            </a>

            <!-- Sales Summary -->
            <a class="nav-link {{ request()->routeIs('reports.sales-summary') ? 'active' : '' }}" href="{{ route('reports.sales-summary') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-chart-column"></i></div>
                Sales Summary
            </a>

            <!-- Category Sales -->
            <a class="nav-link {{ request()->routeIs('reports.category-sales') ? 'active' : '' }}" href="{{ route('reports.category-sales') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                Category Sales
            </a>

            <!-- Profit & Margin -->
            <a class="nav-link {{ request()->routeIs('reports.margin') ? 'active' : '' }}" href="{{ route('reports.margin') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-coins"></i></div>
                Profit &amp; Margin
            </a>

            <div class="sb-sidenav-menu-heading">Content Management</div>

            <!-- Banner Management -->
            <a class="nav-link {{ request()->routeIs('banner.*') ? 'active' : '' }}" href="{{ route('banner.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-image"></i></div>
                Banners
            </a>

            <!-- Flash Sales -->
            <a class="nav-link {{ request()->routeIs('flash-sale.*') ? 'active' : '' }}" href="{{ route('flash-sale.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-bolt"></i></div>
                Flash Sales
            </a>

            <!-- Top Selling Products -->
            <a class="nav-link {{ request()->routeIs('top-selling.*') ? 'active' : '' }}" href="{{ route('top-selling.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-fire"></i></div>
                Top Selling
            </a>

            {{-- Popup Leads --}}
            {{-- <a class="nav-link {{ request()->routeIs('popup-leads.*') ? 'active' : '' }}" href="{{ route('popup-leads.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                Popup Leads
            </a> --}}

            <!-- Coupons -->
            <a class="nav-link {{ request()->routeIs('coupon.*') ? 'active' : '' }}" href="{{ route('coupon.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-ticket"></i></div>
                Coupons
            </a>

            <!-- Category Management -->
            <a class="nav-link {{ request()->routeIs('category.*') ? 'active' : '' }}" href="{{ route('category.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                Categories
            </a>

            <!-- Brand Management -->
            <a class="nav-link {{ request()->routeIs('brand.*') ? 'active' : '' }}" href="{{ route('brand.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-copyright"></i></div>
                Brands
            </a>

            <!-- Product Management -->
            <a class="nav-link {{ request()->routeIs('product.*') ? 'active' : '' }}" href="{{ route('product.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                Products
            </a>

            <div class="sb-sidenav-menu-heading">Product Attributes</div>

            <!-- Specifications -->
            <a class="nav-link {{ request()->routeIs('specification.*') ? 'active' : '' }}" href="{{ route('specification.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-list-alt"></i></div>
                Specifications
            </a>

            <div class="sb-sidenav-menu-heading">Addons</div>

            <!-- Attribute Names -->
            <a class="nav-link {{ request()->routeIs('attribute-name.*') ? 'active' : '' }}" href="{{ route('attribute-name.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-font"></i></div>
                Attribute Names
            </a>

            <!-- Attribute Values -->
            <a class="nav-link {{ request()->routeIs('attribute-value.*') ? 'active' : '' }}" href="{{ route('attribute-value.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-align-left"></i></div>
                Attribute Values
            </a>

            <div class="sb-sidenav-menu-heading">CMS</div>

            <!-- Pages -->
            <a class="nav-link {{ request()->routeIs('page.*') ? 'active' : '' }}" href="{{ route('page.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                Pages
            </a>

            <!-- Blogs -->
            <a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}" href="{{ route('blog.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-blog"></i></div>
                Blogs
            </a>

            <!-- Site Contents -->
            <a class="nav-link {{ request()->routeIs('site-content.*') ? 'active' : '' }}" href="{{ route('site-content.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                Site Content
            </a>

            <!-- Homepage Settings -->
            <a class="nav-link {{ request()->routeIs('homepage-settings.*') ? 'active' : '' }}" href="{{ route('homepage-settings.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-th-large"></i></div>
                Homepage Settingss
            </a>

            <!-- Contact Messages -->
            <a class="nav-link {{ request()->routeIs('contact.*') ? 'active' : '' }}" href="{{ route('contact.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                Contact Messages
                @php $unread = \App\Models\ContactMessage::unreadCount(); @endphp
                @if($unread > 0)
                    <span class="badge bg-warning text-dark ms-auto" style="font-size:11px;">{{ $unread }}</span>
                @endif
            </a>

            {{-- Search Keywords --}}
            {{-- <a class="nav-link {{ request()->routeIs('search-keywords.*') ? 'active' : '' }}" href="{{ route('search-keywords.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-search"></i></div>
                Search Keywords
            </a> --}}

            @if(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']))
            <div class="sb-sidenav-menu-heading">Administration</div>
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>
                Users
            </a>
            @endif

            <div class="sb-sidenav-menu-heading">Configuration</div>
            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-sliders-h"></i></div>
                Settings
            </a>

            <div class="sb-sidenav-menu-heading">Financial</div>

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

            {{-- Balance Sheet --}}
            {{-- <a class="nav-link {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}" href="{{ route('reports.balance-sheet') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>
                Balance Sheet
            </a> --}}

            <!-- Stock Value -->
            <a class="nav-link {{ request()->routeIs('reports.stock-value') ? 'active' : '' }}" href="{{ route('reports.stock-value') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-warehouse"></i></div>
                Stock Value
            </a>
        </div>
    </div>
    <div class="sb-sidenav-footer">
        <div class="small">Logged in as:</div>
        {{\Illuminate\Support\Facades\Auth::user()?->name}}
    </div>
</nav>
