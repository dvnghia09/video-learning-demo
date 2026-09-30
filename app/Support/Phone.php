<?php

namespace App\Support;

/** Chuẩn hoá số điện thoại Việt Nam: bỏ dấu cách/chấm/gạch, đổi +84 / 84 thành 0. */
class Phone
{
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = preg_replace('/[\s.\-()]+/', '', trim($value));

        return preg_match('/^\+?84(\d{9})$/', $v, $m) ? '0'.$m[1] : $v;
    }

    /** Số di động Việt Nam: 10 chữ số, đầu số 03, 05, 07, 08, 09. */
    public static function isValidMobile(?string $value): bool
    {
        return (bool) preg_match('/^0[35789]\d{8}$/', (string) self::normalize($value));
    }
}
