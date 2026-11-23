<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SearchKeyword;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchKeywordController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $keywords = SearchKeyword::query()
            ->when($search !== '', fn ($query) => $query->where('keyword', 'like', "%{$search}%"))
            ->orderByDesc('count')
            ->orderByDesc('updated_at')
            ->paginate(50)
            ->withQueryString();

        return view('backend.modules.search-keyword.index', compact('keywords', 'search'));
    }
}
