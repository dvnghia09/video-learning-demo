<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_three_steps_change_with_the_visitors_state(): void
    {
        // Khách: xem thử miễn phí, đăng ký khi muốn xem VIP, và gợi ý nâng cấp qua Zalo
        $this->get('/')->assertOk()
            ->assertSee('Bắt đầu chỉ với 3 bước')->assertSee('không cần đăng ký')->assertSee('Cần khi bạn muốn xem')
            ->assertSee('Đã có tài khoản mà chưa là VIP?')->assertSee('Cách nâng cấp qua Zalo');

        // Đã đăng nhập nhưng chưa VIP: bước 2 hoàn thành, bước 3 mời nâng cấp
        $user = User::factory()->create(['name' => 'Cô Hoa', 'is_vip' => false]);
        $this->actingAs($user)->get('/')->assertOk()
            ->assertSee('Đã có tài khoản')->assertSee('Cô Hoa')->assertSee('Tài khoản của bạn chưa là VIP')->assertSee('Cách nâng cấp qua Zalo');

        // VIP: cả 3 bước hoàn thành, không còn nút nâng cấp
        $vip = User::factory()->create(['is_vip' => true]);
        $this->actingAs($vip)->get('/')->assertOk()
            ->assertSee('thành viên VIP')->assertSee('Vào học ngay')->assertDontSee('Cách nâng cấp qua Zalo');
    }

    public function test_modal_is_present_and_triggers_are_wired(): void
    {
        Setting::putMany(['zalo_url' => 'https://zalo.me/0901234567', 'phones' => [['number' => '0901234567', 'label' => 'Admin']]]);
        $lesson = Lesson::create(['title' => 'Bài A']);
        $vip = $lesson->videos()->create(['title' => 'Video VIP', 'is_free' => false, 'status' => 'ready']);
        $free = $lesson->videos()->create(['title' => 'Video free', 'is_free' => true, 'status' => 'ready']);

        // Quên mật khẩu mở cửa sổ hướng dẫn (href chỉ là dự phòng)
        $this->get('/dang-nhap')->assertOk()->assertSee('data-help="password"', false)->assertSee('id="helpModal"', false)->assertSee('Quên mật khẩu?');

        // Danh sách bài: video VIP mở cửa sổ, ghi rõ liên hệ Zalo; video miễn phí thì không
        $list = $this->get('/bai-hoc')->assertOk()->assertSee('VIP · cần đăng nhập rồi nâng cấp gói')->assertSee('Cách nâng cấp');
        $this->assertStringContainsString('data-help="upgrade"', $list->getContent());

        // Trang xem video VIP: nói rõ cần nâng cấp và liên hệ Zalo; không nhảy thẳng sang Zalo
        $page = $this->get(route("courses.video", [$lesson, $vip]))->assertOk()
            ->assertSee('Video dành cho thành viên VIP')->assertSee('2 việc')->assertSee('Đăng nhập')->assertSee('đăng ký')
            ->assertSee('Liên hệ quản trị viên qua Zalo')->assertSee('Xem hướng dẫn nâng cấp qua Zalo')->assertSee(route('register'), false);
        $this->assertMatchesRegularExpression('/zalo.{0,40}0901234567/s', $page->getContent());   // cấu hình Zalo đã đưa vào cửa sổ hướng dẫn
        $this->get(route("courses.video", [$lesson, $free]))->assertOk()->assertDontSee('Video dành cho thành viên VIP');

        // Đã có tài khoản mà chưa VIP: chỉ cần nâng cấp, không bị nhắc đăng ký nữa
        $member = User::factory()->create(['name' => 'Bác Ba', 'is_vip' => false]);
        $this->actingAs($member)->get(route("courses.video", [$lesson, $vip]))->assertOk()
            ->assertSee('Bác Ba')->assertSee('chưa là thành viên VIP')->assertSee('Xem cách nâng cấp qua Zalo')->assertDontSee('2 việc');
        // Danh sách bài: khách được nhắc "cần đăng nhập rồi nâng cấp", người đã đăng nhập chỉ nhắc nâng cấp
        auth()->logout();
        $this->get('/bai-hoc')->assertSee('cần đăng nhập rồi nâng cấp gói');
        $this->actingAs($member)->get('/bai-hoc')->assertSee('Cần nâng cấp gói · liên hệ Zalo')->assertDontSee('cần đăng nhập rồi nâng cấp gói');

        // Tài khoản: nút nâng cấp mở cửa sổ (chưa VIP), VIP thì không có
        $user = User::factory()->create(['is_vip' => false]);
        $this->actingAs($user)->get('/tai-khoan')->assertOk()->assertSee('data-help="upgrade"', false)->assertSee('Nâng cấp qua Zalo');
        $this->actingAs(User::factory()->create(['is_vip' => true]))->get('/tai-khoan')->assertOk()->assertDontSee('data-help="upgrade"', false);
    }

    public function test_modal_falls_back_when_zalo_is_not_configured(): void
    {
        $page = $this->get('/dang-nhap')->assertOk();
        $this->assertStringContainsString('\u0022zalo\u0022:null', $page->getContent());   // JS sẽ ẩn nút Zalo và hiện "Gửi lời nhắn cho admin"
        $this->assertStringContainsString('id="helpContact"', $page->getContent());
    }
}
