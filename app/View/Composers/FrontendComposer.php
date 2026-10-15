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

        // Top level categories only — sub categories are rendered inside the
        // hover dropdown of their parent.
        $view->with('navCategories', Category::where('status', Category::STATUS_ACTIVE)
            ->whereNull('category_id')
            ->with(['children' => static fn ($query) => $query
                ->where('status', Category::STATUS_ACTIVE)
                ->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->limit(8)
            ->get()
        );
    }
}
