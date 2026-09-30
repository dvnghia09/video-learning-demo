<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnToTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_to_the_page_the_user_came_from(): void
    {
        $user = User::factory()->create(['phone' => '0911000111']);

        $this->from('/khoa-hoc/1/video/2')->get('/dang-nhap')->assertOk();
        $this->post('/dang-nhap', ['phone' => '0911000111', 'password' => 'password'])->assertRedirect(url('/khoa-hoc/1/video/2'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_returns_to_the_page_the_user_came_from(): void
    {
        $this->from('/bai-viet/3')->get('/dang-ky')->assertOk();
        $this->post('/dang-ky', ['name' => 'An', 'phone' => '0922000222', 'password' => 'matkhau-123', 'password_confirmation' => 'matkhau-123'])
            ->assertRedirect(url('/bai-viet/3'));
    }

    public function test_page_is_kept_when_switching_between_login_and_register_or_after_a_failed_login(): void
    {
        User::factory()->create(['phone' => '0933000333']);

        $this->from('/khoa-hoc/1/video/2')->get('/dang-nhap');
        $this->from('/dang-nhap')->get('/dang-ky')->assertOk();             // từ Đăng nhập sang Đăng ký
        $this->from('/dang-ky')->get('/dang-nhap')->assertOk();
        $this->from('/dang-nhap')->post('/dang-nhap', ['phone' => '0933000333', 'password' => 'sai-mat-khau']); // đăng nhập sai
        $this->from('/dang-nhap')->get('/dang-nhap')->assertOk();

        $this->post('/dang-nhap', ['phone' => '0933000333', 'password' => 'password'])->assertRedirect(url('/khoa-hoc/1/video/2'));
    }

    public function test_falls_back_to_home_and_ignores_other_websites(): void
    {
        User::factory()->create(['phone' => '0944000444']);

        $this->get('/dang-nhap')->assertOk(); // không có trang trước
        $this->post('/dang-nhap', ['phone' => '0944000444', 'password' => 'password'])->assertRedirect(url('/'));
        auth()->logout();

        $this->withHeader('Referer', 'https://evil.example/phish')->get('/dang-nhap')->assertOk();
        $this->post('/dang-nhap', ['phone' => '0944000444', 'password' => 'password'])->assertRedirect(url('/'));
    }

    public function test_protected_pages_still_return_after_login(): void
    {
        User::factory()->create(['phone' => '0955000555', 'role' => 'admin']);

        $this->get('/admin/settings')->assertRedirect('/dang-nhap');
        $this->get('/dang-nhap')->assertOk();
        $this->post('/dang-nhap', ['phone' => '0955000555', 'password' => 'password'])->assertRedirect(url('/admin/settings'));
    }
}
