<?php

namespace App\Rules;

use App\Support\Phone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VietnamesePhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Phone::isValidMobile($value)) {
            $fail('Số điện thoại chưa đúng. Hãy nhập số di động 10 chữ số, bắt đầu bằng 03, 05, 07, 08 hoặc 09 (ví dụ: 0912345678).');
        }
    }
}
