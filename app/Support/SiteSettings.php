<?php

namespace App\Support;

class SiteSettings
{
    public function __construct(private array $data = []) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->data[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public function brand(): string
    {
        return $this->get('brand_name', 'FloralArt');
    }

    /** @return array<int, array{number:string,label:string}> Số đầu tiên là số mặc định. */
    public function phones(): array
    {
        $list = json_decode($this->get('phones', '[]'), true);

        return collect(is_array($list) ? $list : [])
            ->filter(fn ($p) => ! empty($p['number']))
            ->map(fn ($p) => ['number' => (string) $p['number'], 'label' => (string) ($p['label'] ?? '')])
            ->values()
            ->all();
    }

    public function defaultPhone(): ?string
    {
        return $this->phones()[0]['number'] ?? null;
    }

    public static function telHref(string $number): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', $number);
    }

    /** Zalo có thể nhập là link hoặc chỉ số điện thoại. */
    public function zaloUrl(): ?string
    {
        $v = trim((string) $this->get('zalo_url', ''));
        if ($v === '') {
            return null;
        }

        return preg_match('/^\d[\d\s.\-]{7,}$/', $v) ? 'https://zalo.me/'.preg_replace('/\D/', '', $v) : $v;
    }

    public function messengerUrl(): ?string
    {
        return $this->get('messenger_link');
    }

    public function fileUrl(string $key): ?string
    {
        $path = $this->get($key);

        return $path ? asset('storage/'.$path) : null;
    }

    public function logoUrl(): ?string
    {
        return $this->fileUrl('logo');
    }

    public function faviconUrl(): ?string
    {
        return $this->fileUrl('favicon');
    }

    public function ogImageUrl(): ?string
    {
        return $this->fileUrl('og_image');
    }
}
