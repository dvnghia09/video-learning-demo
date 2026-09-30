<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Rules\VietnamesePhone;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'name' => is_string($request->name) ? trim(preg_replace('/\s+/u', ' ', $request->name)) : $request->name,
            'phone' => is_string($request->phone) ? Phone::normalize($request->phone) : $request->phone,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['nullable', 'string', new VietnamesePhone],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
        ], [
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'message.required' => 'Vui lòng nhập nội dung bạn muốn gửi.',
            'message.min' => 'Nội dung quá ngắn, vui lòng nhập ít nhất 5 ký tự.',
        ]);

        Contact::create($data);

        return redirect()->route('contact.index')->with('success', 'Cảm ơn bạn! Chúng tôi sẽ liên hệ lại sớm nhất.');
    }
}
