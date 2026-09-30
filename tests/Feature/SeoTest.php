<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_slugs_are_vietnamese_and_unique(): void
    {
        $a = Blog::create(['title' => 'Cắm hoa đẹp', 'content' => 'x']);
        $b = Blog::create(['title' => 'Cắm hoa đẹp', 'content' => 'x']);
        $this->assertSame('cam-hoa-dep', $a->slug);
        $this->assertSame('cam-hoa-dep-2', $b->slug);
    }

    public function test_blog_id_redirects_permanently_to_slug(): void
    {
        $blog = Blog::create(['title' => 'Bài mẫu', 'content' => 'x']);
        $this->get('/bai-viet/'.$blog->id)->assertStatus(301)->assertRedirect(route('blogs.show', $blog->slug));
        $this->get('/bai-viet/khong-ton-tai')->assertNotFound();
    }

    public function test_legacy_english_urls_redirect(): void
    {
        $lesson = Lesson::create(['title' => 'Bài A']);
        $v = $lesson->videos()->create(['title' => 'Video B']);
        $this->get('/khoa-hoc')->assertStatus(301)->assertRedirect('/bai-hoc');
        $this->get('/login')->assertStatus(301)->assertRedirect('/dang-nhap');
        $this->get("/khoa-hoc/{$lesson->id}/video/{$v->id}")->assertStatus(301)->assertRedirect(route('courses.video', [$lesson, $v]));
    }

    public function test_sitemap_and_robots(): void
    {
        $blog = Blog::create(['title' => 'Bài sitemap', 'content' => 'x']);
        $lesson = Lesson::create(['title' => 'L']);
        $ready = $lesson->videos()->create(['title' => 'Sẵn sàng', 'status' => 'ready']);
        $pending = $lesson->videos()->create(['title' => 'Đang xử lý', 'status' => 'processing']);

        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();
        $this->assertStringContainsString(route('blogs.show', $blog->slug), $xml);
        $this->assertStringContainsString(route('courses.video', [$lesson, $ready]), $xml);
        $this->assertStringNotContainsString(route('courses.video', [$lesson, $pending]), $xml);

        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.url('/sitemap.xml'))->assertSee('Disallow: /admin');
    }

    public function test_head_has_canonical_robots_and_jsonld(): void
    {
        $blog = Blog::create(['title' => 'Bài SEO', 'content' => 'Nội dung']);
        $html = $this->get(route('blogs.show', $blog->slug))->assertOk()->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="'.route('blogs.show', $blog->slug).'">', $html);
        $this->assertStringContainsString('"@type":"Article"', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
        $this->assertStringNotContainsString('<?php', $html);

        $this->get('/dang-nhap')->assertSee('content="noindex,nofollow"', false);
    }

    public function test_friendly_404(): void
    {
        $this->get('/khong-co-trang-nay')->assertNotFound()->assertSee('Không tìm thấy trang');
    }
}
