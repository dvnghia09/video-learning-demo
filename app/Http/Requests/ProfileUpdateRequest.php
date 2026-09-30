<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\VietnamesePhone;
use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /** Tự chuẩn hoá trước khi kiểm tra: email viết thường, số điện thoại bỏ dấu cách/chấm/gạch. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'phone' => is_string($this->phone) ? Phone::normalize($this->phone) : $this->phone,
            'email' => is_string($this->email) ? mb_strtolower(trim($this->email)) : $this->email,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', new VietnamesePhone, Rule::unique(User::class)->ignore($this->user()->id)],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại này đã được dùng cho tài khoản khác.',
            'email.email' => 'Email chưa đúng định dạng.',
            'email.unique' => 'Email này đã được dùng cho tài khoản khác.',
            'avatar.image' => 'File phải là hình ảnh.',
            'avatar.mimes' => 'Ảnh phải là JPG, PNG hoặc WebP.',
            'avatar.max' => 'Ảnh tối đa 4MB.',
        ];
    }
}
