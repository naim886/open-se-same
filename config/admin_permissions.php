<?php

/*
|--------------------------------------------------------------------------
| Admin permission registry
|--------------------------------------------------------------------------
|
| One source of truth for the backend permission catalogue. Permission names
| are "{module}.{action}" so they can be grouped by module without any extra
| database column. Everything here is cache-friendly: the whole array is
| static and is compiled once per process.
|
| New permissions added here are picked up by `PermissionSeeder` (idempotent)
| and become available in the role matrix immediately after seeding.
|
*/

return [

    'guard' => 'web',

    // This role bypasses every check and cannot be edited or deleted.
    'super_role' => 'Super Admin',

    /*
    |--------------------------------------------------------------------------
    | Module catalogue
    |--------------------------------------------------------------------------
    | 'permissions' => [action => human label]
    */
    'modules' => [

        'dashboard' => [
            'label' => 'Dashboard',
            'icon'  => 'tachometer-alt',
            'permissions' => [
                'view' => 'View dashboard',
            ],
        ],

        'orders' => [
            'label' => 'Orders',
            'icon'  => 'shopping-cart',
            'permissions' => [
                'view'    => 'View orders',
                'create'  => 'Create orders / POS',
                'update'  => 'Edit orders',
                'delete'  => 'Delete orders',
                'status'  => 'Change order status',
                'note'    => 'Add order notes',
                'courier' => 'Send to courier',
                'invoice' => 'Invoice & shipping label',
            ],
        ],

        'customers' => [
            'label' => 'Customers',
            'icon'  => 'users',
            'permissions' => [
                'view'   => 'View customers',
                'update' => 'Edit customers & order source',
                'export' => 'Export customer list',
                'tags'   => 'Manage customer tags',
                'notes'  => 'Manage customer notes',
            ],
        ],

        'products' => [
            'label' => 'Products',
            'icon'  => 'box',
            'permissions' => [
                'view'   => 'View products',
                'create' => 'Create products',
                'update' => 'Edit products',
                'delete' => 'Delete products',
                'import' => 'Import products (CSV)',
            ],
        ],

        'categories' => [
            'label' => 'Categories',
            'icon'  => 'tags',
            'permissions' => [
                'view'   => 'View categories',
                'create' => 'Create categories',
                'update' => 'Edit categories',
                'delete' => 'Delete categories',
            ],
        ],

        'brands' => [
            'label' => 'Brands',
            'icon'  => 'copyright',
            'permissions' => [
                'view'   => 'View brands',
                'create' => 'Create brands',
                'update' => 'Edit brands',
                'delete' => 'Delete brands',
            ],
        ],

        'attributes' => [
            'label' => 'Attributes',
            'icon'  => 'font',
            'permissions' => [
                'view'   => 'View attribute names & values',
                'create' => 'Create attributes',
                'update' => 'Edit attributes',
                'delete' => 'Delete attributes',
            ],
        ],

        'specifications' => [
            'label' => 'Specifications',
            'icon'  => 'list-alt',
            'permissions' => [
                'view'   => 'View specifications',
                'create' => 'Create specifications',
                'update' => 'Edit specifications',
                'delete' => 'Delete specifications',
            ],
        ],

        'banners' => [
            'label' => 'Banners',
            'icon'  => 'image',
            'permissions' => [
                'view'   => 'View banners',
                'create' => 'Create banners',
                'update' => 'Edit banners',
                'delete' => 'Delete banners',
            ],
        ],

        'flash_sales' => [
            'label' => 'Flash Sales',
            'icon'  => 'bolt',
            'permissions' => [
                'view'   => 'View flash sales',
                'create' => 'Create flash sales',
                'update' => 'Edit flash sales',
                'delete' => 'Delete flash sales',
            ],
        ],

        'top_selling' => [
            'label' => 'Top Selling',
            'icon'  => 'fire',
            'permissions' => [
                'view'   => 'View top selling list',
                'create' => 'Add top selling products',
                'update' => 'Reorder / edit top selling',
                'delete' => 'Remove top selling products',
            ],
        ],

        'coupons' => [
            'label' => 'Coupons',
            'icon'  => 'ticket-alt',
            'permissions' => [
                'view'   => 'View coupons',
                'create' => 'Create coupons',
                'update' => 'Edit / toggle coupons',
                'delete' => 'Delete coupons',
            ],
        ],

        'homepage' => [
            'label' => 'Homepage Settings',
            'icon'  => 'th-large',
            'permissions' => [
                'view'   => 'View homepage builder',
                'update' => 'Edit homepage blocks',
            ],
        ],

        'site_content' => [
            'label' => 'Site Content',
            'icon'  => 'cog',
            'permissions' => [
                'view'   => 'View site content',
                'create' => 'Create site content',
                'update' => 'Edit site content',
                'delete' => 'Delete site content',
            ],
        ],

        'pages' => [
            'label' => 'Pages',
            'icon'  => 'file-alt',
            'permissions' => [
                'view'   => 'View pages',
                'create' => 'Create pages',
                'update' => 'Edit pages',
                'delete' => 'Delete pages',
            ],
        ],

        'blogs' => [
            'label' => 'Blogs',
            'icon'  => 'blog',
            'permissions' => [
                'view'   => 'View blogs',
                'create' => 'Create blogs',
                'update' => 'Edit blogs',
                'delete' => 'Delete blogs',
            ],
        ],

        'contact' => [
            'label' => 'Contact Messages',
            'icon'  => 'envelope',
            'permissions' => [
                'view'   => 'View messages',
                'update' => 'Mark messages as read',
                'delete' => 'Delete messages',
            ],
        ],

        'popup_leads' => [
            'label' => 'Popup Leads',
            'icon'  => 'clipboard-list',
            'permissions' => [
                'view'   => 'View popup leads',
                'delete' => 'Delete popup leads',
            ],
        ],

        'reports' => [
            'label' => 'Reports',
            'icon'  => 'chart-line',
            'permissions' => [
                'sales'      => 'Sales report',
                'summary'    => 'Sales summary',
                'categories' => 'Category sales',
                'products'   => 'Product performance',
                'margin'     => 'Profit & margin',
                'stock'      => 'Stock value',
                'commission' => 'Commission report',
                'balance'    => 'Balance sheet',
            ],
        ],

        'accounts' => [
            'label' => 'Accounts',
            'icon'  => 'wallet',
            'permissions' => [
                'view'   => 'View accounts',
                'create' => 'Add transactions',
                'delete' => 'Delete transactions',
            ],
        ],

        'expenses' => [
            'label' => 'Expenses',
            'icon'  => 'money-bill-wave',
            'permissions' => [
                'view'   => 'View expenses',
                'create' => 'Create expenses',
                'update' => 'Edit expenses',
                'delete' => 'Delete expenses',
            ],
        ],

        'search_keywords' => [
            'label' => 'Search Keywords',
            'icon'  => 'search',
            'permissions' => [
                'view' => 'View search keywords',
            ],
        ],

        'media' => [
            'label' => 'Media',
            'icon'  => 'photo-video',
            'permissions' => [
                'upload' => 'Upload images from the editor',
            ],
        ],

        'users' => [
            'label' => 'Staff & Users',
            'icon'  => 'user-shield',
            'permissions' => [
                'view'   => 'View users',
                'update' => 'Change status & role',
            ],
        ],

        'roles' => [
            'label' => 'Roles & Permissions',
            'icon'  => 'key',
            'permissions' => [
                'view'   => 'View roles & permission matrix',
                'manage' => 'Create roles & change permissions',
            ],
        ],

        'settings' => [
            'label' => 'Settings',
            'icon'  => 'sliders-h',
            'permissions' => [
                'view'   => 'View settings',
                'update' => 'Change settings',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Route => permission
    |--------------------------------------------------------------------------
    | Explicit entries for routes that do not follow the resource convention.
    | Everything else is inferred: {route}.{action} => {module}.{action}.
    */
    'route_permissions' => [
        'dashboard.index' => 'dashboard.view',

        'order_dependency'         => 'orders.create',
        'orders.filter'            => 'orders.view',
        'orders.updateStatus'      => 'orders.status',
        'orders.addNote'           => 'orders.note',
        'orders.sendToCourier'     => 'orders.courier',
        'orders.invoice'           => 'orders.invoice',
        'orders.label'             => 'orders.invoice',
        'orders.documents'         => 'orders.invoice',

        'product.import'       => 'products.import',
        'product.import.store' => 'products.import',

        'customers.export'         => 'customers.export',
        'customers.order-source'   => 'customers.update',
        'customers.tags.index'     => 'customers.tags',
        'customers.tags.store'     => 'customers.tags',
        'customers.tags.update'    => 'customers.tags',
        'customers.tags.destroy'   => 'customers.tags',
        'customers.tags.attach'    => 'customers.tags',
        'customers.tags.detach'    => 'customers.tags',
        'customers.notes.store'    => 'customers.notes',
        'customers.notes.destroy'  => 'customers.notes',

        'contact.markRead'   => 'contact.update',
        'coupon.toggle'      => 'coupons.update',

        'flash-sale.search-products'        => 'flash_sales.view',
        'flash-sale.products-by-category'   => 'flash_sales.view',
        'top-selling.search-products'       => 'top_selling.view',
        'top-selling.products-by-category'  => 'top_selling.view',
        'top-selling.reorder'               => 'top_selling.update',

        'reports.sales'              => 'reports.sales',
        'reports.sales-summary'      => 'reports.summary',
        'reports.category-sales'     => 'reports.categories',
        'reports.product-performance' => 'reports.products',
        'reports.margin'             => 'reports.margin',
        'reports.stock-value'        => 'reports.stock',
        'reports.commission'         => 'reports.commission',
        'reports.balance-sheet'      => 'reports.balance',

        'homepage-settings.index'  => 'homepage.view',
        'homepage-settings.update' => 'homepage.update',

        'accounts.index'   => 'accounts.view',
        'accounts.store'   => 'accounts.create',
        'accounts.destroy' => 'accounts.delete',

        'search-keywords.index' => 'search_keywords.view',

        'tinymce.upload' => 'media.upload',

        'admin.users.index'         => 'users.view',
        'admin.users.toggleStatus'  => 'users.update',
        'admin.users.updateRole'    => 'users.update',

        'roles.index'      => 'roles.view',
        'roles.store'      => 'roles.manage',
        'roles.update'     => 'roles.manage',
        'roles.destroy'    => 'roles.manage',
        'permissions.index' => 'roles.view',
        'permissions.sync'  => 'roles.manage',

        'settings.index'  => 'settings.view',
        'settings.update' => 'settings.update',

        // Utility action: available to anyone who can change settings.
        'cache.clear' => 'settings.update',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route prefix => module
    |--------------------------------------------------------------------------
    | Used when inferring the permission of a resource route, e.g.
    | "category.update" => "categories.update".
    */
    'route_modules' => [
        'dashboard'       => 'dashboard',
        'orders'          => 'orders',
        'order'           => 'orders',
        'customers'       => 'customers',
        'product'         => 'products',
        'category'        => 'categories',
        'brand'           => 'brands',
        'attribute-name'  => 'attributes',
        'attribute-value' => 'attributes',
        'specification'   => 'specifications',
        'banner'          => 'banners',
        'flash-sale'      => 'flash_sales',
        'top-selling'     => 'top_selling',
        'coupon'          => 'coupons',
        'homepage-settings' => 'homepage',
        'site-content'    => 'site_content',
        'page'            => 'pages',
        'blog'            => 'blogs',
        'contact'         => 'contact',
        'popup-leads'     => 'popup_leads',
        'accounts'        => 'accounts',
        'expense'         => 'expenses',
        'search-keywords' => 'search_keywords',
        'tinymce'         => 'media',
        'admin'           => 'users',
        'roles'           => 'roles',
        'permissions'     => 'roles',
        'settings'        => 'settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource action => permission action
    |--------------------------------------------------------------------------
    */
    'route_actions' => [
        'index'   => 'view',
        'show'    => 'view',
        'filter'  => 'view',
        'create'  => 'create',
        'store'   => 'create',
        'edit'    => 'update',
        'update'  => 'update',
        'toggle'  => 'update',
        'markRead' => 'update',
        'reorder' => 'update',
        'attach'  => 'update',
        'detach'  => 'update',
        'destroy' => 'delete',
        'export'  => 'export',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default permissions per role (used by PermissionSeeder once)
    |--------------------------------------------------------------------------
    | '*' grants everything, 'module.*' grants every action of that module.
    | These are only applied the first time a role receives permissions;
    | after that the matrix is fully managed from the UI.
    */
    'default_roles' => [

        'Super Admin' => '*',

        'Admin' => '*',

        'Manager' => [
            'dashboard.view',
            'orders.view', 'orders.create', 'orders.update', 'orders.status',
            'orders.note', 'orders.courier', 'orders.invoice',
            'customers.view', 'customers.update', 'customers.export',
            'customers.tags', 'customers.notes',
            'products.view', 'products.create', 'products.update', 'products.import',
            'categories.*', 'brands.*', 'attributes.*', 'specifications.*',
            'banners.*', 'flash_sales.*', 'top_selling.*', 'coupons.*',
            'homepage.view', 'homepage.update',
            'site_content.*', 'pages.*', 'blogs.*',
            'contact.view', 'contact.update',
            'popup_leads.view',
            'reports.*',
            'accounts.view', 'expenses.*',
            'search_keywords.view',
            'media.upload',
            'settings.view',
            'users.view',
        ],

        'Operation' => [
            'dashboard.view',
            'orders.view', 'orders.create', 'orders.update', 'orders.status',
            'orders.note', 'orders.courier', 'orders.invoice',
            'customers.view', 'customers.update', 'customers.tags', 'customers.notes',
            'products.view',
            'categories.view', 'brands.view',
            'coupons.view',
            'reports.sales', 'reports.summary',
            'media.upload',
        ],

        'Sales Executive' => [
            'dashboard.view',
            'orders.view', 'orders.create',
            'customers.view', 'customers.export',
            'products.view',
            'coupons.view',
            'reports.sales',
        ],

    ],

];
