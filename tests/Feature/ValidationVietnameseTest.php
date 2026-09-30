<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationVietnameseTest extends TestCase
{
    use RefreshDatabase;

    private function register(array $over = [])
    {
        return $this->post('/dang-ky', array_merge([
            'name' => 'Nguyễn Thị Lan', 'phone' => '0912345678', 'password' => 'matkhau-123', 'password_confirmation' => 'matkhau-123',
        ], $over));
    }

    private function errors(string $key): string
    {
        return implode(' | ', session('errors')?->get($key) ?? []);
    }

    public function test_language_pack_is_complete_and_default_locale_is_vietnamese(): void
    {
        $this->assertSame('vi', app()->getLocale());

        $en = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');
        $vi = require lang_path('vi/validation.php');
        $this->assertSame([], array_values(array_diff(array_keys($en), array_keys($vi))), 'Thiếu khoá dịch trong lang/vi/validation.php');
        foreach (['auth', 'pagination', 'passwords'] as $file) {
            $this->assertSame([], array_values(array_diff_key(require base_path("vendor/laravel/framework/src/Illuminate/Translation/lang/en/$file.php"), require lang_path("vi/$file.php"))));
        }
    }

    public function test_phone_helper_normalises_and_validates_vietnamese_mobiles(): void
    {
        foreach (['0912345678', '0912 345 678', '091.234.5678', '091-234-5678', '+84912345678', '84912345678', '(0912) 345678'] as $ok) {
            $this->assertSame('0912345678', Phone::normalize($ok), $ok);
            $this->assertTrue(Phone::isValidMobile($ok), $ok);
        }
        foreach (['', '123', 'abcdefghij', '0123456789', '0212345678', '091234567', '09123456789', '+8412345678', '0912a45678'] as $bad) {
            $this->assertFalse(Phone::isValidMobile($bad), $bad);
        }
    }

    public function test_register_requires_valid_phone_with_vietnamese_messages(): void
    {
        foreach (['123', 'abcdefghij', '0123456789', '091234567'] as $bad) {
            $this->register(['phone' => $bad])->assertSessionHasErrors('phone');
            $this->assertStringContainsString('Số điện thoại chưa đúng', $this->errors('phone'), $bad);
        }
        $this->assertGuest();
    }

    public function test_register_accepts_and_normalises_common_phone_formats(): void
    {
        $this->register(['phone' => '+84 912 345 678'])->assertSessionHasNoErrors();
        $this->assertNotNull(User::where('phone', '0912345678')->first());
        auth()->logout();

        // Cùng số viết kiểu khác vẫn bị coi là trùng
        $this->register(['phone' => '0912.345.678', 'name' => 'Người khác'])->assertSessionHasErrors('phone');
        $this->assertStringContainsString('đã được đăng ký', $this->errors('phone'));
    }

    public function test_register_password_needs_8_characters_and_confirmation(): void
    {
        $this->register(['password' => '1234567', 'password_confirmation' => '1234567'])->assertSessionHasErrors('password');
        $this->assertSame('Mật khẩu phải có ít nhất 8 ký tự.', $this->errors('password'));

        $this->register(['password_confirmation' => 'khac-mat-khau'])->assertSessionHasErrors('password');
        $this->assertStringContainsString('Mật khẩu nhập lại chưa khớp', $this->errors('password'));

        $this->register(['password' => '12345678', 'password_confirmation' => '12345678'])->assertSessionHasNoErrors();   // đúng 8 ký tự là đủ
    }

    public function test_register_required_fields_and_name_rules_are_in_vietnamese(): void
    {
        $this->register(['name' => '', 'phone' => '', 'password' => '', 'password_confirmation' => ''])->assertSessionHasErrors(['name', 'phone', 'password']);
        $this->assertSame('Vui lòng nhập họ và tên.', $this->errors('name'));
        $this->assertSame('Vui lòng nhập số điện thoại.', $this->errors('phone'));
        $this->assertSame('Vui lòng nhập mật khẩu.', $this->errors('password'));

        $this->register(['name' => 'A'])->assertSessionHasErrors('name');
        $this->assertStringContainsString('ít nhất 2 ký tự', $this->errors('name'));
        $this->register(['name' => 'Lan 123 <b>'])->assertSessionHasErrors('name');
        $this->assertStringContainsString('chỉ nên gồm chữ cái', $this->errors('name'));
    }

    public function test_login_messages_and_phone_formats(): void
    {
        User::factory()->create(['phone' => '0987001122']);

        $this->post('/dang-nhap', ['phone' => '0987001122', 'password' => 'sai-mat-khau'])->assertSessionHasErrors('phone');
        $this->assertSame('Số điện thoại hoặc mật khẩu chưa đúng. Vui lòng kiểm tra lại.', $this->errors('phone'));

        $this->post('/dang-nhap', ['phone' => '', 'password' => ''])->assertSessionHasErrors(['phone', 'password']);
        $this->assertSame('Vui lòng nhập số điện thoại.', $this->errors('phone'));
        $this->assertSame('Vui lòng nhập mật khẩu.', $this->errors('password'));

        // Gõ kiểu "+84 987 001 122" hay "0987 001 122" đều đăng nhập được
        $this->post('/dang-nhap', ['phone' => '+84 987 001 122', 'password' => 'password'])->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    public function test_login_lockout_message_is_vietnamese(): void
    {
        User::factory()->create(['phone' => '0987001133']);
        foreach (range(1, 5) as $i) {
            $this->post('/dang-nhap', ['phone' => '0987001133', 'password' => 'sai']);
        }
        $this->post('/dang-nhap', ['phone' => '0987001133', 'password' => 'sai'])->assertSessionHasErrors('phone');
        $this->assertStringContainsString('đăng nhập sai quá nhiều lần', $this->errors('phone'));
    }

    public function test_profile_validation_messages(): void
    {
        $user = User::factory()->create(['phone' => '0966000111']);
        User::factory()->create(['phone' => '0966000222']);

        $this->actingAs($user)->patch('/tai-khoan', ['name' => 'A', 'phone' => '123', 'email' => 'khong-phai-email'])->assertSessionHasErrors(['name', 'phone', 'email']);
        $this->assertStringContainsString('ít nhất 2 ký tự', $this->errors('name'));
        $this->assertStringContainsString('Số điện thoại chưa đúng', $this->errors('phone'));
        $this->assertStringContainsString('Email chưa đúng định dạng', $this->errors('email'));

        $this->actingAs($user)->patch('/tai-khoan', ['name' => 'An', 'phone' => '0966 000 222'])->assertSessionHasErrors('phone');
        $this->assertStringContainsString('đã được dùng cho tài khoản khác', $this->errors('phone'));
    }

    public function test_change_password_messages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/tai-khoan')->put('/tai-khoan/mat-khau', ['current_password' => 'sai-roi', 'password' => '1234567', 'password_confirmation' => '1234567'])
            ->assertSessionHasErrorsIn('updatePassword', ['current_password', 'password']);
        $bag = session('errors')->getBag('updatePassword');
        $this->assertSame('Mật khẩu hiện tại chưa đúng.', $bag->first('current_password'));
        $this->assertSame('Mật khẩu phải có ít nhất 8 ký tự.', $bag->first('password'));

        $this->actingAs($user)->from('/tai-khoan')->put('/tai-khoan/mat-khau', ['current_password' => 'password', 'password' => 'matkhau-moi-1', 'password_confirmation' => 'khac'])
            ->assertSessionHasErrorsIn('updatePassword', 'password');
        $this->assertStringContainsString('nhập lại chưa khớp', session('errors')->getBag('updatePassword')->first('password'));
    }

    public function test_contact_form_messages(): void
    {
        $this->post('/lien-he', ['name' => '', 'phone' => '12345', 'message' => ''])->assertSessionHasErrors(['name', 'phone', 'message']);
        $this->assertSame('Vui lòng nhập họ và tên.', $this->errors('name'));
        $this->assertStringContainsString('Số điện thoại chưa đúng', $this->errors('phone'));
        $this->assertSame('Vui lòng nhập nội dung bạn muốn gửi.', $this->errors('message'));

        $this->post('/lien-he', ['name' => 'Lan', 'message' => 'abc'])->assertSessionHasErrors('message');
        $this->assertStringContainsString('ít nhất 5 ký tự', $this->errors('message'));

        // Số điện thoại là tuỳ chọn; nhập kiểu nào cũng được lưu chuẩn
        $this->post('/lien-he', ['name' => 'Lan', 'message' => 'Cho tôi hỏi khóa học'])->assertSessionHasNoErrors();
        $this->post('/lien-he', ['name' => 'Lan', 'phone' => '+84 912 345 678', 'message' => 'Cho tôi hỏi khóa học'])->assertSessionHasNoErrors();
        $this->assertSame('0912345678', \App\Models\Contact::latest('id')->first()->phone);
    }

    public function test_admin_forms_use_vietnamese_messages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/users', ['name' => '', 'phone' => '999', 'password' => '123', 'role' => 'user'])->assertSessionHasErrors(['name', 'phone', 'password']);
        $this->assertSame('Vui lòng nhập họ tên.', $this->errors('name'));
        $this->assertStringContainsString('Số điện thoại chưa đúng', $this->errors('phone'));
        $this->assertSame('Mật khẩu tối thiểu 8 ký tự.', $this->errors('password'));

        // Form không tự viết thông báo riêng vẫn ra tiếng Việt nhờ bộ ngôn ngữ mặc định
        $this->actingAs($admin)->post('/admin/lessons', ['title' => '', 'order' => 'abc'])->assertSessionHasErrors(['title', 'order']);
        $this->assertSame('Vui lòng nhập tiêu đề.', $this->errors('title'));
        $this->assertSame('Thứ tự phải là số nguyên.', $this->errors('order'));

        $this->actingAs($admin)->post('/admin/videos', [])->assertSessionHasErrors(['title', 'lesson_id', 'video_file']);
        $this->assertSame('Vui lòng nhập tên video.', $this->errors('title'));

        $this->actingAs($admin)->put('/admin/settings', ['email' => 'sai', 'google_analytics' => 'x'])->assertSessionHasErrors(['email', 'google_analytics']);
        $this->assertStringContainsString('định dạng email', $this->errors('email'));
    }

    public function test_pagination_and_error_text_are_vietnamese(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(12)->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee('kết quả')->assertSee('Hiển thị')->assertDontSee('Showing')->assertDontSee('results');
        $this->get('/khong-co-trang-nay')->assertNotFound();
    }
}
