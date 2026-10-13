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
        $keywords = SearchKeyword::orderByDesc('count')
            ->orderByDesc('updated_at')
            ->paginate(50);

        return view('backend.modules.search-keyword.index', compact('keywords'));
    }
}
