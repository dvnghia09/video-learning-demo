<?php

namespace App\Http\Requests\Auth;

use App\Rules\VietnamesePhone;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /** Tự chuẩn hoá trước khi kiểm tra: bỏ khoảng trắng thừa, số điện thoại về dạng 0xxxxxxxxx. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim(preg_replace('/\s+/u', ' ', $this->name)) : $this->name,
            'phone' => is_string($this->phone) ? Phone::normalize($this->phone) : $this->phone,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\p{L}\p{M}\s.'’-]+$/u"],
            'phone' => ['required', 'string', new VietnamesePhone, Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'confirmed', Password::defaults(), 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'name.regex' => 'Họ và tên chỉ nên gồm chữ cái, không nhập số hoặc ký tự đặc biệt.',
            'phone.unique' => 'Số điện thoại này đã được đăng ký. Bạn hãy đăng nhập, hoặc dùng số điện thoại khác.',
            'password.confirmed' => 'Mật khẩu nhập lại chưa khớp. Vui lòng nhập lại cho đúng.',
        ];
    }
}
