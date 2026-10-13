<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Page;
use Illuminate\View\View;

class FrontendComposer
{
    /**
     * Bind data to the frontend layout view.
     */
    public function compose(View $view): void
    {
        $view->with('footerPages', Page::where('status', Page::STATUS_ACTIVE)
            ->select('title', 'slug')
            ->orderBy('title')
            ->get()
        );

        $view->with('navCategories', Category::where('status', Category::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->limit(8)
            ->get()
        );
    }
}
