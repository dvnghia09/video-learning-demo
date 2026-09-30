<?php

namespace Tests\Feature;

use App\Models\User;
use App\Jobs\ProcessVideo;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_normal_users_cannot_access_admin(): void
    {
        $this->get('/admin')->assertRedirect('/dang-nhap');
        $this->actingAs(User::factory()->create())->get('/admin')->assertRedirect('/');
    }

    public function test_admin_pages_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['', '/users', '/banners', '/pages', '/contacts', '/lessons', '/videos', '/blogs'] as $path) {
            $this->actingAs($admin)->get('/admin'.$path)->assertOk();
        }
        foreach (['users', 'banners', 'pages', 'lessons', 'videos', 'blogs'] as $r) {
            $this->actingAs($admin)->get("/admin/$r/create")->assertOk();
        }
    }

    public function test_admin_crud_with_checkbox_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/lessons', ['title' => 'L1', 'order' => 1, 'is_active' => 'on'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.lessons.index'));
        $this->actingAs($admin)->post('/admin/pages', ['slug' => 'about', 'title' => 'About', 'content' => 'x'])
            ->assertSessionHasNoErrors();
        $this->actingAs($admin)->post('/admin/users', ['name' => 'N', 'phone' => '0911111111', 'password' => 'secret123', 'role' => 'user', 'is_vip' => 'on'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(User::where('phone', '0911111111')->first()->is_vip);
        $this->get('/gioi-thieu')->assertOk()->assertSee('About');
    }

    public function test_video_upload_returns_json_and_queues_processing(): void
    {
        Storage::fake('local');
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $lesson = Lesson::create(['title' => 'L']);

        $this->actingAs($admin)->postJson('/admin/videos', [
            'title' => 'V',
            'lesson_id' => $lesson->id,
            'is_free' => '1',
            'video_file' => UploadedFile::fake()->create('a.mp4', 500, 'video/mp4'),
        ])->assertOk()->assertJson(['success' => true, 'redirect' => route('admin.videos.index')]);

        Queue::assertPushed(ProcessVideo::class);
        $video = $lesson->videos()->first();
        $this->assertTrue($video->is_free);
        Storage::disk('local')->assertExists($video->video_path);

        $this->actingAs($admin)->get('/admin/videos')->assertOk()->assertSee('Đang chờ xử lý');
        $this->actingAs($admin)->getJson('/admin/videos/status?ids='.$video->id)->assertOk()->assertJsonPath($video->id.'.status', 'pending');
        $this->actingAs($admin)->get("/admin/videos/{$video->id}/edit")->assertOk();
        $this->actingAs($admin)->get("/admin/lessons/{$lesson->id}/edit")->assertOk()->assertSee('V');

        $this->actingAs($admin)->delete("/admin/videos/{$video->id}")->assertRedirect();
        Storage::disk('local')->assertMissing($video->video_path);
    }

    public function test_video_upload_rejects_non_video(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $lesson = Lesson::create(['title' => 'L']);

        $this->actingAs($admin)->postJson('/admin/videos', [
            'title' => 'V', 'lesson_id' => $lesson->id,
            'video_file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
        ])->assertStatus(422)->assertJsonValidationErrors('video_file');
    }

    public function test_banner_upload_validation_and_listing(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/banners', [])->assertSessionHasErrors('image');
        $this->actingAs($admin)->post('/admin/banners', ['image' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])->assertSessionHasErrors('image');

        $this->actingAs($admin)->post('/admin/banners', ['image' => UploadedFile::fake()->image('b.jpg', 1920, 900), 'is_active' => '1'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.banners.index'));

        $banner = \App\Models\Banner::first();
        Storage::disk('public')->assertExists($banner->image_path);
        $this->actingAs($admin)->get('/admin/banners')->assertOk()->assertSee('Đang hiển thị');
        $this->get('/')->assertOk()->assertSee('data-slide', false);

        $this->actingAs($admin)->delete("/admin/banners/{$banner->id}")->assertRedirect();
        Storage::disk('public')->assertMissing($banner->image_path);
    }

    public function test_artworks_admin_crud_reorder_and_homepage(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        // chưa có tác phẩm: mục ẩn khỏi trang chủ
        $this->get('/')->assertOk()->assertDontSee('Các tác phẩm nổi bật');

        $this->actingAs($admin)->post('/admin/artworks', [])->assertSessionHasErrors('images');
        $this->actingAs($admin)->post('/admin/artworks', ['images' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]])->assertSessionHasErrors('images.0');

        // thêm nhiều ảnh một lúc
        $this->actingAs($admin)->post('/admin/artworks', ['images' => [
            UploadedFile::fake()->image('a.jpg', 2400, 1600), UploadedFile::fake()->image('b.png', 800, 800), UploadedFile::fake()->image('c.jpg', 900, 900),
        ]])->assertSessionHasNoErrors()->assertRedirect(route('admin.artworks.index'));

        $items = \App\Models\Artwork::ordered()->get();
        $this->assertCount(3, $items);
        $this->assertSame([1, 2, 3], $items->pluck('sort_order')->all());
        foreach ($items as $it) { Storage::disk('public')->assertExists($it->image_path); }
        [$a, $b, $c] = $items->all();

        // ảnh lớn được thu nhỏ
        [$w] = getimagesizefromstring(Storage::disk('public')->get($a->image_path));
        $this->assertLessThanOrEqual(1600, $w);

        // đặt tiêu đề + hiện trên trang chủ
        $this->actingAs($admin)->put("/admin/artworks/{$a->id}", ['title' => 'Lẵng hoa đẹp', 'sort_order' => 1, 'is_active' => '1'])->assertSessionHasNoErrors();
        $this->get('/')->assertOk()->assertSee('Các tác phẩm nổi bật')->assertSee('Lẵng hoa đẹp')->assertSee($b->url(), false);

        // ẩn -> không hiện
        $this->actingAs($admin)->post("/admin/artworks/{$b->id}/toggle");
        $this->get('/')->assertDontSee($b->url(), false);

        // đổi thứ tự: đưa c lên trước a
        $this->actingAs($admin)->post("/admin/artworks/{$c->id}/move", ['dir' => 'up']);
        $this->actingAs($admin)->post("/admin/artworks/{$c->id}/move", ['dir' => 'up']);
        $this->assertSame($c->id, \App\Models\Artwork::ordered()->first()->id);

        // xoá -> xoá cả file
        $this->actingAs($admin)->delete("/admin/artworks/{$a->id}")->assertRedirect();
        Storage::disk('public')->assertMissing($a->image_path);
        $this->actingAs($admin)->get('/admin/artworks')->assertOk();
        $this->actingAs($admin)->get('/admin/artworks/create')->assertOk();
        $this->actingAs($admin)->get("/admin/artworks/{$c->id}/edit")->assertOk();
    }

    public function test_contacts_show_vietnamese_status_and_can_be_updated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $c = \App\Models\Contact::create(['name' => 'Lan', 'phone' => '0900000000', 'message' => 'Tư vấn giúp mình', 'status' => 'new']);
        \App\Models\Contact::create(['name' => 'Mai', 'phone' => '0911111111', 'message' => 'Đã đọc rồi', 'status' => 'read']);

        // Danh sách: nhãn tiếng Việt, không lộ giá trị tiếng Anh
        $page = $this->actingAs($admin)->get('/admin/contacts')->assertOk();
        $page->assertSee('Mới')->assertSee('Đã xem')->assertSee('Đã liên hệ lại')->assertSee('Khách hàng');
        $this->assertStringNotContainsString('>new<', $page->getContent());
        $this->assertStringNotContainsString('>read<', $page->getContent());

        // Lọc theo trạng thái
        $this->actingAs($admin)->get('/admin/contacts?status=new')->assertSee('Tư vấn giúp mình')->assertDontSee('Đã đọc rồi');
        $this->actingAs($admin)->get('/admin/contacts?status=abc')->assertOk()->assertSee('Tư vấn giúp mình')->assertSee('Đã đọc rồi');

        // Sidebar hiện số liên hệ mới
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('bg-rose-500', false);

        // Mở xem: Mới -> Đã xem
        $this->actingAs($admin)->get("/admin/contacts/{$c->id}")->assertOk()->assertSee('Đã xem')->assertSee('Đánh dấu đã liên hệ lại');
        $this->assertSame('read', $c->fresh()->status);

        // Đánh dấu thủ công + mở lại không hạ trạng thái
        $this->actingAs($admin)->patch("/admin/contacts/{$c->id}/status", ['status' => 'handled'])->assertSessionHasNoErrors();
        $this->assertSame('handled', $c->fresh()->status);
        $this->actingAs($admin)->get("/admin/contacts/{$c->id}")->assertSee('Đã liên hệ lại');
        $this->assertSame('handled', $c->fresh()->status);

        $this->actingAs($admin)->patch("/admin/contacts/{$c->id}/status", ['status' => 'bậy'])->assertSessionHasErrors('status');
        $this->assertSame('Không rõ', (new \App\Models\Contact(['status' => 'x']))->statusLabel());
    }

    public function test_lessons_list_shows_videos_with_thumbnails_and_videos_list_has_thumbnails(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $lesson = Lesson::create(['title' => 'Bài có video', 'order' => 1]);
        $empty = Lesson::create(['title' => 'Bài trống', 'order' => 2]);
        $ready = $lesson->videos()->create(['title' => 'Video xong', 'is_free' => true, 'status' => 'ready', 'hls_path' => 'videos/5_hls/master.m3u8']);
        $busy = $lesson->videos()->create(['title' => 'Video đang xử lý', 'is_free' => false, 'status' => 'processing']);

        $this->assertNotNull($ready->posterUrl());
        $this->assertStringContainsString("/stream/{$ready->id}/poster.jpg", $ready->posterUrl());
        $this->assertNull($busy->posterUrl());

        // /admin/lessons: bài + danh sách video có ảnh thu nhỏ, khung chờ cho video chưa xong, bài trống có lời mời tải video
        $page = $this->actingAs($admin)->get('/admin/lessons')->assertOk();
        $page->assertSee('Bài có video')->assertSee('Video xong')->assertSee('Video đang xử lý')
            ->assertSee("data-thumb=\"{$ready->id}\"", false)->assertSee("/stream/{$ready->id}/poster.jpg", false)
            ->assertSee('Đang xử lý')->assertSee('Bài học này chưa có video nào.')->assertSee('Mở tất cả')
            ->assertSee('admin\\/videos\\/status', false);   // có polling vì còn video đang xử lý

        // /admin/videos: mỗi video có ảnh thu nhỏ
        $this->actingAs($admin)->get('/admin/videos')->assertOk()
            ->assertSee("data-thumb=\"{$ready->id}\"", false)->assertSee("/stream/{$ready->id}/poster.jpg", false)->assertSee("data-thumb=\"{$busy->id}\"", false);

        // API trạng thái trả về ảnh khi xong
        $this->actingAs($admin)->getJson('/admin/videos/status?ids='.$ready->id.','.$busy->id)->assertOk()
            ->assertJsonPath("{$ready->id}.poster", $ready->posterUrl())->assertJsonPath("{$busy->id}.poster", null);
    }

    public function test_users_admin_labels_search_filters_vip_toggle_and_protections(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Quản Trị', 'phone' => '0900000001']);
        $vip = User::factory()->create(['name' => 'Bà Lan', 'phone' => '0900000002', 'is_vip' => true]);
        $free = User::factory()->create(['name' => 'Bác Tư', 'phone' => '0900000003', 'is_vip' => false]);

        // Cột "Gói học" hiển thị chữ rõ nghĩa, không phải 1/trống
        $page = $this->actingAs($admin)->get('/admin/users')->assertOk();
        $page->assertSee('Thành viên VIP')->assertSee('Miễn phí')->assertSee('Quản trị viên')->assertSee('Học viên')->assertSee('Nâng cấp VIP')->assertSee('Hạ về miễn phí');

        // Tìm kiếm + lọc
        $this->actingAs($admin)->get('/admin/users?q=0900000003')->assertSee('Bác Tư')->assertDontSee('Bà Lan');
        $this->actingAs($admin)->get('/admin/users?type=vip')->assertSee('Bà Lan')->assertDontSee('Bác Tư');
        $this->actingAs($admin)->get('/admin/users?type=free')->assertSee('Bác Tư')->assertDontSee('Bà Lan');
        $this->actingAs($admin)->get('/admin/users?type=admin')->assertSee('Quản Trị')->assertDontSee('Bác Tư');

        // Nâng cấp / hạ gói nhanh
        $this->actingAs($admin)->post("/admin/users/{$free->id}/vip")->assertSessionHas('success');
        $this->assertTrue($free->fresh()->is_vip);
        $this->actingAs($admin)->post("/admin/users/{$free->id}/vip");
        $this->assertFalse($free->fresh()->is_vip);

        // Không tự xoá mình, không tự bỏ quyền admin
        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertSessionHas('error');
        $this->assertNotNull($admin->fresh());
        $this->actingAs($admin)->put("/admin/users/{$admin->id}", ['name' => 'Quản Trị', 'phone' => '0900000001', 'role' => 'user'])->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);

        // Tạo mới: bắt buộc mật khẩu ≥ 8 ký tự, số điện thoại hợp lệ và không trùng
        $this->actingAs($admin)->post('/admin/users', ['name' => 'X', 'phone' => 'abc', 'password' => '123', 'role' => 'user'])->assertSessionHasErrors(['phone', 'password']);
        $this->actingAs($admin)->post('/admin/users', ['name' => 'X', 'phone' => '0900000002', 'password' => 'matkhau-1234', 'role' => 'user'])->assertSessionHasErrors('phone');
        $this->actingAs($admin)->post('/admin/users', ['name' => 'Cô Mai', 'phone' => '0900000009', 'password' => 'matkhau-1234', 'role' => 'user', 'is_vip' => '1'])->assertSessionHasNoErrors();
        $new = User::where('phone', '0900000009')->first();
        $this->assertTrue($new->is_vip);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('matkhau-1234', $new->password));

        // Sửa: để trống mật khẩu thì giữ nguyên
        $old = $new->password;
        $this->actingAs($admin)->put("/admin/users/{$new->id}", ['name' => 'Cô Mai 2', 'phone' => '0900000009', 'role' => 'user'])->assertSessionHasNoErrors();
        $this->assertSame($old, $new->fresh()->password);
        $this->assertFalse($new->fresh()->is_vip);   // bỏ tick VIP = hạ về miễn phí
        $this->actingAs($admin)->get("/admin/users/{$new->id}/edit")->assertOk()->assertSee('Thành viên VIP');
    }

    public function test_blogs_and_pages_lists_are_informative(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        \App\Models\Blog::create(['title' => 'Cách cắm hoa hồng', 'content' => '<p>Nội dung <b>rất hay</b> về hoa hồng</p>']);
        \App\Models\Blog::create(['title' => 'Mẹo giữ hoa tươi', 'content' => '<p>Thay nước mỗi ngày</p>']);
        \App\Models\Page::create(['slug' => 'about', 'title' => 'Giới thiệu', 'content' => '<p>Về FloralArt</p>']);
        \App\Models\Page::create(['slug' => 'chinh-sach', 'title' => 'Chính sách', 'content' => '<p>Bảo mật</p>']);

        $this->actingAs($admin)->get('/admin/blogs')->assertOk()->assertSee('Cách cắm hoa hồng')->assertSee('Nội dung rất hay về hoa hồng')->assertSee('Chưa có ảnh đại diện')->assertSee('Viết bài mới');
        $this->actingAs($admin)->get('/admin/blogs?q=giữ')->assertSee('Mẹo giữ hoa tươi')->assertDontSee('Cách cắm hoa hồng');
        $this->actingAs($admin)->get('/admin/blogs?q=khongcogi')->assertSee('Không tìm thấy bài viết phù hợp');

        $this->actingAs($admin)->get('/admin/pages')->assertOk()->assertSee('Trên menu')->assertSee('Ở footer')->assertSee('/gioi-thieu')->assertSee('/trang/chinh-sach')->assertSee('Về FloralArt');
    }
}
