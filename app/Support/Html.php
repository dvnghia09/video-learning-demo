<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Lọc HTML do trình soạn thảo (CKEditor) tạo ra trước khi hiển thị cho khách:
 * chỉ giữ các thẻ/thuộc tính an toàn, bỏ script, sự kiện on*, style, và các đường link javascript:.
 */
class Html
{
    private const TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'h5', 'blockquote', 'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'hr', 'span', 'div', 'sub', 'sup', 'code', 'pre'];

    private const ATTRS = ['href', 'src', 'alt', 'title', 'width', 'height', 'target', 'rel', 'colspan', 'rowspan'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $doc->getElementById('__root');
        if (! $root) {
            return e($html);
        }
        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    /**
     * Ảnh dán/kéo vào trình soạn thảo đôi khi ở dạng base64 (data:image/...;base64,...) làm phình database.
     * Hàm này lưu chúng thành file trên disk public và thay bằng đường link /storage/...
     */
    public static function extractInlineImages(?string $html, string $dir = 'uploads/content'): string
    {
        $html = (string) $html;
        if (! str_contains($html, 'data:image/')) {
            return $html;
        }

        return preg_replace_callback('#(<img\b[^>]*?\bsrc\s*=\s*)(["\'])data:image/(png|jpe?g|gif|webp);base64,([A-Za-z0-9+/=\s]+)\2#i', function ($m) use ($dir) {
            $bin = base64_decode(preg_replace('/\s+/', '', $m[4]), true);
            if ($bin === false || strlen($bin) > 8 * 1024 * 1024) {
                return $m[1].$m[2].$m[2]; // ảnh hỏng hoặc quá lớn: bỏ nguồn ảnh
            }
            $ext = strtolower($m[3]) === 'jpeg' ? 'jpg' : strtolower($m[3]);
            $path = trim($dir, '/').'/'.\Illuminate\Support\Str::random(40).'.'.$ext;
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $bin);

            return $m[1].$m[2].parse_url(asset('storage/'.$path), PHP_URL_PATH).$m[2];
        }, $html) ?? $html;
    }

    /** Đoạn trích thuần văn bản, dùng cho danh sách bài viết và meta description. */
    public static function excerpt(?string $html, int $limit = 150): string
    {
        $html = preg_replace('#<(script|style)\b.*?</\1\s*>#is', '', (string) $html); // bỏ cả nội dung trong script/style
        $html = preg_replace('#</(p|h[1-6]|li|div|tr|blockquote)>|<br\s*/?>#i', ' ', $html); // tách chữ giữa các đoạn
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $text)));

        return \Illuminate\Support\Str::limit($text, $limit);
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }
            $tag = strtolower($child->tagName);

            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'link', 'meta', 'svg', 'math'], true)) {
                $node->removeChild($child); // xoá luôn cả nội dung bên trong

                continue;
            }
            if (! in_array($tag, self::TAGS, true)) {
                // thẻ lạ: giữ nội dung con, bỏ thẻ
                self::walk($child);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                $name = strtolower($attr->name);
                $val = trim($attr->value);
                $ok = in_array($name, self::ATTRS, true);
                if ($ok && in_array($name, ['href', 'src'], true)) {
                    $ok = (bool) preg_match('#^(https?:|mailto:|tel:|/|\#)#i', $val) && ! preg_match('#^\s*(javascript|data|vbscript):#i', $val);
                }
                if (! $ok) {
                    $child->removeAttribute($attr->name);
                }
            }
            if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                $child->setAttribute('rel', 'noopener noreferrer');
            }
            if ($tag === 'img') {
                $child->setAttribute('loading', 'lazy');
            }
            self::walk($child);
        }
    }
}
