<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Page;
use App\Models\User;
use App\Support\Html;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mP8z8BQz0AEYBxVSF+FABJADveWkH6oAAAAAElFTkSuQmCC';

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_editor_image_upload_returns_a_link(): void
    {
        Storage::fake('public');

        $res = $this->actingAs($this->admin())->postJson('/admin/uploads/image', ['file' => UploadedFile::fake()->image('a.png', 300, 200)])->assertOk();

        $location = $res->json('location');
        $this->assertStringStartsWith('/storage/uploads/content/', $location);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $location));
    }

    public function test_editor_upload_rejects_non_images_and_guests(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->postJson('/admin/uploads/image', ['file' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])->assertStatus(422);
        $this->actingAs($this->admin())->postJson('/admin/uploads/image', ['file' => UploadedFile::fake()->create('x.svg', 10, 'image/svg+xml')])->assertStatus(422);
        auth()->logout();
        $this->postJson('/admin/uploads/image', ['file' => UploadedFile::fake()->image('a.png')])->assertStatus(401);
    }

    public function test_base64_images_are_converted_to_files_when_saving(): void
    {
        Storage::fake('public');
        $html = '<p>Xin chào</p><img src="data:image/png;base64,'.self::PNG.'" alt="a">';

        $this->actingAs($this->admin())->post('/admin/blogs', ['title' => 'Bài có ảnh', 'content' => $html])->assertSessionHasNoErrors();
        $this->actingAs($this->admin())->post('/admin/pages', ['title' => 'Trang', 'slug' => 'trang-anh', 'content' => $html])->assertSessionHasNoErrors();

        foreach ([Blog::first()->content, Page::first()->content] as $saved) {
            $this->assertStringNotContainsString('base64', $saved);
            $this->assertMatchesRegularExpression('#src="/storage/uploads/content/[A-Za-z0-9]+\.png"#', $saved);
        }
        $this->assertCount(2, Storage::disk('public')->files('uploads/content'));
    }

    public function test_html_is_sanitised_for_visitors(): void
    {
        $dirty = '<p onclick="x()">Chào <b>bạn</b></p><script>alert(1)</script><a href="javascript:alert(1)">bad</a><a href="https://ok.vn" target="_blank">ok</a><img src="/storage/a.png" onerror="x()"><iframe src="https://evil.example"></iframe>';
        $clean = Html::clean($dirty);

        $this->assertStringContainsString('<b>bạn</b>', $clean);
        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('iframe', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
        $this->assertStringContainsString('src="/storage/a.png"', $clean);
    }

    public function test_blog_and_pages_render_html_content_on_the_site(): void
    {
        $blog = Blog::create(['title' => 'Bài HTML', 'content' => '<h2>Mục 1</h2><p>Nội dung <strong>đậm</strong></p><script>alert(1)</script>']);
        Page::create(['slug' => 'about', 'title' => 'Giới thiệu', 'content' => '<p>Xin chào FloralArt</p>']);
        Page::create(['slug' => 'chinh-sach', 'title' => 'Chính sách', 'content' => '<ul><li>Điều 1</li></ul>']);

        $this->get('/bai-viet/'.$blog->slug)->assertOk()->assertSee('<h2>Mục 1</h2>', false)->assertSee('<strong>đậm</strong>', false)->assertDontSee('alert(1)', false);
        $this->get('/bai-viet')->assertOk()->assertSee('Nội dung đậm')->assertDontSee('<h2>', false);
        $this->get('/gioi-thieu')->assertOk()->assertSee('Xin chào FloralArt');
        $this->get('/trang/chinh-sach')->assertOk()->assertSee('<li>Điều 1</li>', false);
        $this->get('/trang/khong-co')->assertNotFound();
        $this->get('/')->assertSee('/trang/chinh-sach', false);   // link ở footer
    }

    public function test_page_slug_validation_and_blog_image_removal(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/pages', ['title' => 'A', 'slug' => 'Có Dấu Cách'])->assertSessionHasErrors('slug');
        $this->actingAs($admin)->post('/admin/pages', ['title' => 'A', 'slug' => 'trung-lap'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post('/admin/pages', ['title' => 'B', 'slug' => 'trung-lap'])->assertSessionHasErrors('slug');
        $this->actingAs($admin)->post('/admin/blogs', ['title' => '', 'content' => ''])->assertSessionHasErrors(['title', 'content']);

        $this->actingAs($admin)->post('/admin/blogs', ['title' => 'Có ảnh', 'content' => '<p>x</p>', 'image' => UploadedFile::fake()->image('c.jpg', 800, 500)]);
        $blog = Blog::first();
        Storage::disk('public')->assertExists($blog->image_path);

        $this->actingAs($admin)->put("/admin/blogs/{$blog->id}", ['title' => 'Có ảnh', 'content' => '<p>x</p>', 'remove_image' => '1'])->assertSessionHasNoErrors();
        $this->assertNull($blog->fresh()->image_path);
        Storage::disk('public')->assertMissing($blog->image_path);
    }

    public function test_admin_forms_render_with_editor(): void
    {
        $admin = $this->admin();
        $blog = Blog::create(['title' => 'X', 'content' => '<p>x</p>']);
        $page = Page::create(['slug' => 'p', 'title' => 'P', 'content' => '<p>x</p>']);

        foreach (['/admin/blogs/create', "/admin/blogs/{$blog->id}/edit", '/admin/pages/create', "/admin/pages/{$page->id}/edit"] as $url) {
            $this->actingAs($admin)->get($url)->assertOk()->assertSee('tinymce', false)->assertDontSee('ckeditor', false);
        }
    }
}
