<?php

namespace App\Support;

class JsonLd
{
    /** Trả về thẻ <script type="application/ld+json"> đã được escape an toàn. */
    public static function tag(array $data): string
    {
        $json = json_encode(['@context' => 'https://schema.org'] + $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);

        return '<script type="application/ld+json">'.$json.'</script>';
    }
}
