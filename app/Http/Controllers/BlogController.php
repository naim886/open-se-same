<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class BlogController extends Controller
{
    public function index(): View
    {
        $blogs = (new Blog())->getBlogs();

        $cms_content = [
            'page_title'   => 'Blogs',
            'module'       => 'Blog',
            'module_url'   => route('blog.index'),
            'sub_module'   => 'List',
            'button_title' => 'Add Blog',
            'button_url'   => route('blog.create'),
            'type'         => 'create',
        ];

        return view('backend.modules.blog.index', compact('cms_content', 'blogs'));
    }

    public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Create Blog',
            'module'       => 'Blog',
            'module_url'   => route('blog.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Blog List',
            'button_url'   => route('blog.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.blog.create', compact('cms_content'));
    }

    public function store(StoreBlogRequest $request): RedirectResponse
    {
        try {
            $blog = (new Blog())->storeBlog($request);
            success_alert($blog->title . ' created successfully.');
            return redirect()->route('blog.index');
        } catch (Throwable $e) {
            app_error_log('Blog Store', $e);
            failed_alert('Something went wrong. ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(Blog $blog): View
    {
        $blog->load('faqs');

        $cms_content = [
            'page_title'   => 'Blog Details',
            'module'       => 'Blog',
            'module_url'   => route('blog.index'),
            'sub_module'   => 'Details',
            'button_title' => 'Blog List',
            'button_url'   => route('blog.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.blog.show', compact('cms_content', 'blog'));
    }

    public function edit(Blog $blog): View
    {
        $blog->load('faqs');

        $cms_content = [
            'page_title'   => 'Edit Blog',
            'module'       => 'Blog',
            'module_url'   => route('blog.index'),
            'sub_module'   => 'Edit',
            'button_title' => 'Blog List',
            'button_url'   => route('blog.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.blog.edit', compact('cms_content', 'blog'));
    }

    public function update(UpdateBlogRequest $request, Blog $blog): RedirectResponse
    {
        try {
            $blog->updateBlog($request);
            success_alert($blog->title . ' updated successfully.');
            return redirect()->route('blog.index');
        } catch (Throwable $e) {
            app_error_log('Blog Update', $e);
            failed_alert('Something went wrong. ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        try {
            if ($blog->cover_photo) {
                \Storage::disk('public')->delete($blog->cover_photo);
            }
            $blog->faqs()->delete();
            $blog->delete();
            success_alert($blog->title . ' deleted successfully.');
        } catch (Throwable $e) {
            app_error_log('Blog Destroy', $e);
            failed_alert('Something went wrong. ' . $e->getMessage());
        }

        return redirect()->route('blog.index');
    }
}
