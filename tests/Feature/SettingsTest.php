<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function save(array $data = [])
    {
        return $this->actingAs($this->admin())->put('/admin/settings', $data);
    }

    public function test_only_admin_can_open_settings(): void
    {
        $this->get('/admin/settings')->assertRedirect('/dang-nhap');
        $this->actingAs(User::factory()->create())->get('/admin/settings')->assertRedirect('/');
        $this->actingAs($this->admin())->get('/admin/settings')->assertOk()->assertSee('Lưu tất cả cài đặt');
    }

    public function test_floating_buttons_hidden_when_nothing_configured(): void
    {
        $this->get('/')->assertOk()->assertDontSee('class="fc-wrap"', false)->assertDontSee('Gọi ngay');
    }

    public function test_settings_show_up_on_the_site(): void
    {
        $this->save([
            'brand_name' => 'Hoa Việt',
            'company_name' => 'Công ty Hoa Việt',
            'address' => '16 Hà Nội',
            'email' => 'hi@hoaviet.vn',
            'phones_number' => ['0868359518', '', '0862639528'],
            'phones_label' => ['Sale Định', '', 'Sale Vân Anh'],
            'zalo_url' => '0362903530',
            'messenger_link' => 'https://m.me/hoaviet',
            'facebook_url' => 'https://facebook.com/hoaviet',
            'meta_title' => 'Học cắm hoa online',
            'meta_description' => 'Khoá học cắm hoa cho mọi nhà',
            'meta_keywords' => 'cắm hoa, học cắm hoa',
            'og_title' => 'Chia sẻ: Hoa Việt',
            'google_analytics' => 'g-ab12cd34',
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/settings');

        $home = $this->get('/')->assertOk();
        $home->assertSee('<title>Học cắm hoa online</title>', false)
            ->assertSee('<meta name="description" content="Khoá học cắm hoa cho mọi nhà">', false)
            ->assertSee('<meta name="keywords" content="cắm hoa, học cắm hoa">', false)
            ->assertSee('<meta property="og:title" content="Chia sẻ: Hoa Việt">', false)
            ->assertSee('<meta property="og:site_name" content="Hoa Việt">', false)
            ->assertSee('G-AB12CD34')
            ->assertSee('Công ty Hoa Việt')
            ->assertSee('href="tel:0868359518"', false)
            ->assertSee('Sale Vân Anh')
            ->assertSee('https://zalo.me/0362903530', false)
            ->assertSee('https://m.me/hoaviet', false)
            ->assertSee('fc-btn fc-call', false)->assertSee('fc-btn fc-zalo', false)->assertSee('fc-btn fc-mess', false);

        $this->assertCount(2, Setting::bag()->phones());
        $this->assertSame('0868359518', Setting::bag()->defaultPhone());
    }

    public function test_each_floating_button_appears_only_if_configured(): void
    {
        $this->save(['zalo_url' => 'https://zalo.me/123456789']);

        $this->get('/')->assertSee('fc-btn fc-zalo', false)->assertDontSee('fc-btn fc-call', false)->assertDontSee('fc-btn fc-mess', false);
    }

    public function test_blank_value_removes_setting(): void
    {
        $this->save(['brand_name' => 'A', 'zalo_url' => '0362903530']);
        $this->save(['brand_name' => 'A', 'zalo_url' => '']);

        $this->get('/')->assertDontSee('fc-btn fc-zalo', false);
    }

    public function test_iframes_are_sanitised(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/settings', ['map_iframe' => '<iframe src="https://evil.example.com/x"></iframe>'])
            ->assertSessionHasErrors('map_iframe');
        $this->actingAs($admin)->put('/admin/settings', ['map_iframe' => '<script>alert(1)</script>'])
            ->assertSessionHasErrors('map_iframe');
        $this->actingAs($admin)->put('/admin/settings', ['fanpage_iframe' => '<iframe src="https://www.google.com/maps/embed?pb=1"></iframe>'])
            ->assertSessionHasErrors('fanpage_iframe');

        $this->actingAs($admin)->put('/admin/settings', [
            'map_iframe' => '<iframe src="https://www.google.com/maps?q=20.95,105.82&z=17&output=embed" width="600" height="450" onload="alert(1)" style="x"></iframe>',
        ])->assertSessionHasNoErrors();

        $this->get('/lien-he')->assertOk()
            ->assertSee('src="https://www.google.com/maps?q=20.95,105.82&amp;z=17&amp;output=embed"', false)
            ->assertDontSee('onload=', false);
    }

    public function test_validation_rejects_bad_input(): void
    {
        $this->save(['google_analytics' => 'abc', 'phones_number' => ['hello'], 'zalo_url' => 'not a link', 'email' => 'x'])
            ->assertSessionHasErrors(['google_analytics', 'phones_number.0', 'zalo_url', 'email']);
    }

    public function test_images_upload_replace_and_remove(): void
    {
        Storage::fake('public');

        $this->save(['logo' => UploadedFile::fake()->image('logo.png', 200, 80), 'og_image' => UploadedFile::fake()->image('og.jpg', 1200, 630)])
            ->assertSessionHasNoErrors();

        $logo = Setting::bag()->get('logo');
        Storage::disk('public')->assertExists($logo);
        $this->get('/')->assertSee(Setting::bag()->logoUrl(), false)->assertSee('<meta property="og:image"', false);

        $this->save(['logo' => UploadedFile::fake()->image('new.png')]);
        Storage::disk('public')->assertMissing($logo);

        $new = Setting::bag()->get('logo');
        $this->save(['remove_logo' => '1']);
        Storage::disk('public')->assertMissing($new);
        $this->assertNull(Setting::bag()->logoUrl());

        $this->save(['logo' => UploadedFile::fake()->create('evil.svg', 5, 'image/svg+xml')])->assertSessionHasErrors('logo');
    }

    public function test_blog_page_uses_its_own_seo(): void
    {
        $blog = Blog::create(['title' => 'Cách cắm hoa hồng', 'content' => 'Nội dung <b>bài viết</b> rất hay']);

        $this->get('/bai-viet/'.$blog->slug)->assertOk()
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<meta property="og:title" content="Cách cắm hoa hồng', false)
            ->assertSee('<meta name="description" content="Nội dung bài viết rất hay">', false);
    }
}
