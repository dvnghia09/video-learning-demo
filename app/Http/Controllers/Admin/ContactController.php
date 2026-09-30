<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $filter = array_key_exists($request->query('status', ''), Contact::STATUSES) ? $request->query('status') : null;

        $items = Contact::query()
            ->when($filter, fn ($q) => $q->where('status', $filter))
            ->latest()->paginate(10)->withQueryString();

        $counts = Contact::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('admin.contacts.index', compact('items', 'filter', 'counts'));
    }

    public function show(Contact $contact): View
    {
        // Mở xem lần đầu thì chuyển "Mới" -> "Đã xem" (không hạ trạng thái "Đã liên hệ lại")
        if ($contact->status === 'new') {
            $contact->update(['status' => 'read']);
        }

        return view('admin.contacts.show', compact('contact'));
    }

    /** Đánh dấu thủ công: chưa xem / đã xem / đã liên hệ lại. */
    public function updateStatus(Request $request, Contact $contact): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:'.implode(',', array_keys(Contact::STATUSES))]]);
        $contact->update($data);

        return back()->with('success', 'Đã chuyển trạng thái thành “'.$contact->statusLabel().'”.');
    }
}
