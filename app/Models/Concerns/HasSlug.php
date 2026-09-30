<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Đường dẫn thân thiện SEO (slug tiếng Việt không dấu) tạo từ tiêu đề.
 * Tự tạo khi thêm mới nếu chưa có; KHÔNG tự đổi khi sửa tiêu đề để địa chỉ bài đã chia sẻ không bị hỏng.
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            if (blank($model->slug)) {
                $model->slug = static::makeUniqueSlug((string) $model->title, $model->getKey());
            }
        });
    }

    public static function makeUniqueSlug(string $text, int|string|null $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($text), 80, '') ?: Str::lower(class_basename(static::class));
        $slug = $base;
        $i = 2;
        while (static::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
