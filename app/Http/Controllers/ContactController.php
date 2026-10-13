<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $messages = (new ContactMessage())->getMessages($request);

        $cms_content = [
            'page_title'   => 'Contact Messages',
            'module'       => 'Contact',
            'module_url'   => route('contact.index'),
            'sub_module'   => 'List',
            'button_title' => null,
            'button_url'   => null,
            'type'         => 'list',
        ];

        return view('backend.modules.contact.index', compact('cms_content', 'messages'));
    }

    public function show(ContactMessage $contact): View
    {
        // Auto mark as read when viewed
        $contact->markAsRead();

        $cms_content = [
            'page_title'   => 'Message Details',
            'module'       => 'Contact',
            'module_url'   => route('contact.index'),
            'sub_module'   => 'Details',
            'button_title' => 'All Messages',
            'button_url'   => route('contact.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.contact.show', compact('cms_content', 'contact'));
    }

    public function destroy(ContactMessage $contact): RedirectResponse
    {
        try {
            $contact->delete();
            success_alert('Message deleted successfully.');
        } catch (Throwable $e) {
            app_error_log('Contact Destroy', $e);
            failed_alert('Could not delete message. ' . $e->getMessage());
        }

        return redirect()->route('contact.index');
    }

    public function markRead(ContactMessage $contact): RedirectResponse
    {
        $contact->markAsRead();
        success_alert('Message marked as read.');
        return redirect()->route('contact.index');
    }
}
