<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/tai-khoan')->assertRedirect('/dang-nhap');
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create(['name' => 'Nguyễn Văn An']);

        $this->actingAs($user)->get('/tai-khoan')->assertOk()
            ->assertSee('Tài khoản của tôi')->assertSee('Nguyễn Văn An')->assertSee('Đổi mật khẩu')->assertSee('Bài đang học');
    }

    public function test_user_can_change_name_phone_and_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/tai-khoan', ['name' => 'Tên Mới', 'phone' => '0987654321', 'email' => 'Moi@Example.com'])
            ->assertSessionHasNoErrors()->assertRedirect('/tai-khoan');

        $user->refresh();
        $this->assertSame('Tên Mới', $user->name);
        $this->assertSame('0987654321', $user->phone);
        $this->assertSame('moi@example.com', $user->email);
    }

    public function test_email_can_be_cleared_and_phone_must_be_unique_and_numeric(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['phone' => '0911111111']);

        $this->actingAs($user)->patch('/tai-khoan', ['name' => 'An', 'phone' => $user->phone, 'email' => ''])->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->email);

        $this->actingAs($user)->patch('/tai-khoan', ['name' => 'An', 'phone' => '0911111111'])->assertSessionHasErrors('phone');
        $this->actingAs($user)->patch('/tai-khoan', ['name' => 'An', 'phone' => 'abc'])->assertSessionHasErrors('phone');
        $this->actingAs($user)->patch('/tai-khoan', ['name' => '', 'phone' => $user->phone])->assertSessionHasErrors('name');
    }

    public function test_avatar_upload_replace_and_remove(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/tai-khoan', ['name' => 'An', 'phone' => $user->phone, 'avatar' => UploadedFile::fake()->image('me.jpg', 1200, 900)])->assertSessionHasNoErrors();
        $first = $user->fresh()->avatar_path;
        Storage::disk('public')->assertExists($first);
        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($first));
        $this->assertLessThanOrEqual(512, max($w, $h));

        $this->get('/')->assertSee($user->fresh()->avatarUrl(), false);   // hiện trên header

        $this->actingAs($user)->patch('/tai-khoan', ['name' => 'An', 'phone' => $user->phone, 'avatar' => UploadedFile::fake()->image('new.png', 300, 300)]);
        Storage::disk('public')->assertMissing($first);

        $second = $user->fresh()->avatar_path;
        $this->actingAs($user)->patch('/tai-khoan', ['name' => 'An', 'phone' => $user->phone, 'remove_avatar' => '1']);
        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('public')->assertMissing($second);

        $this->actingAs($user)->patch('/tai-khoan', ['name' => 'An', 'phone' => $user->phone, 'avatar' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])->assertSessionHasErrors('avatar');
    }

    public function test_password_can_be_changed_only_with_the_correct_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/tai-khoan')->put('/tai-khoan/mat-khau', ['current_password' => 'sai', 'password' => 'matkhau-moi-1', 'password_confirmation' => 'matkhau-moi-1'])
            ->assertSessionHasErrorsIn('updatePassword', 'current_password');

        $this->actingAs($user)->from('/tai-khoan')->put('/tai-khoan/mat-khau', ['current_password' => 'password', 'password' => 'matkhau-moi-1', 'password_confirmation' => 'matkhau-moi-1'])
            ->assertSessionHasNoErrors()->assertRedirect('/tai-khoan');
        $this->assertTrue(Hash::check('matkhau-moi-1', $user->fresh()->password));
    }

    public function test_user_can_delete_their_account_with_password(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->from('/tai-khoan')->delete('/tai-khoan', ['password' => 'sai'])->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull($user->fresh());

        $this->actingAs($user)->delete('/tai-khoan', ['password' => 'password'])->assertRedirect('/');
        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_video_progress_is_saved_resumed_and_shown_on_profile(): void
    {
        $user = User::factory()->create();
        $lesson = Lesson::create(['title' => 'Bài A']);
        $video = $lesson->videos()->create(['title' => 'Video dở', 'is_free' => true, 'status' => 'ready', 'hls_path' => 'videos/9_hls/master.m3u8']);
        $locked = $lesson->videos()->create(['title' => 'Video khoá', 'is_free' => false, 'status' => 'ready']);

        $this->postJson("/videos/{$video->id}/progress", ['position' => 30, 'duration' => 100])->assertStatus(401);           // khách không lưu được
        $this->actingAs($user)->postJson("/videos/{$locked->id}/progress", ['position' => 30, 'duration' => 100])->assertForbidden(); // chưa có quyền xem
        $this->actingAs($user)->postJson("/videos/{$video->id}/progress", ['position' => -1, 'duration' => 100])->assertStatus(422);

        $this->actingAs($user)->postJson("/videos/{$video->id}/progress", ['position' => 40, 'duration' => 100])->assertOk()->assertJson(['percent' => 40]);
        $this->actingAs($user)->postJson("/videos/{$video->id}/progress", ['position' => 55.7, 'duration' => 100])->assertOk();
        $this->assertSame(1, VideoProgress::count());
        $this->assertSame(55, VideoProgress::first()->position);

        // trang xem video nhớ vị trí dở
        $this->actingAs($user)->get(route("courses.video", [$lesson, $video]))->assertOk()->assertSee('SERVER_RESUME = 55', false);
        // hiện ở trang tài khoản
        $this->actingAs($user)->get('/tai-khoan')->assertOk()->assertSee('Video dở')->assertSee('55% đã xem')->assertSee('Tiếp tục');

        // xem xong -> đã hoàn thành, lần sau bắt đầu lại từ đầu
        $this->actingAs($user)->postJson("/videos/{$video->id}/progress", ['position' => 99, 'duration' => 100, 'completed' => true])->assertOk()->assertJson(['percent' => 100]);
        $this->actingAs($user)->get(route("courses.video", [$lesson, $video]))->assertSee('SERVER_RESUME = 0', false);
        $this->actingAs($user)->get('/tai-khoan')->assertSee('Đã xong')->assertSee('Xem lại');
    }
}
