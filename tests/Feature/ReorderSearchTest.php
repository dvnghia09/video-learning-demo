<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderSearchTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_blog_search_filters_and_is_noindex(): void
    {
        Blog::create(['title' => 'Hoa hồng đỏ', 'content' => 'x']);
        Blog::create(['title' => 'Lan hồ điệp', 'content' => 'y']);

        $this->get('/bai-viet?q=hồng')->assertOk()->assertSee('Hoa hồng đỏ')->assertDontSee('Lan hồ điệp')
            ->assertSee('content="noindex,follow"', false);
        $this->get('/bai-viet?q=zzz')->assertOk()->assertSee('Không tìm thấy bài viết phù hợp');
        $this->get('/bai-viet')->assertOk()->assertSee('Lan hồ điệp')->assertDontSee('noindex', false);
    }

    public function test_lessons_can_be_reordered(): void
    {
        $a = Lesson::create(['title' => 'A', 'order' => 1]);
        $b = Lesson::create(['title' => 'B', 'order' => 2]);
        $c = Lesson::create(['title' => 'C', 'order' => 3]);

        $this->actingAs($this->admin())->postJson(route('admin.lessons.reorder'), ['ids' => [$c->id, $a->id, $b->id]])->assertOk();
        $this->assertSame([$c->id, $a->id, $b->id], Lesson::orderBy('order')->pluck('id')->all());
    }

    public function test_videos_reorder_within_lesson_only(): void
    {
        $l = Lesson::create(['title' => 'L']);
        $o = Lesson::create(['title' => 'O']);
        $v1 = $l->videos()->create(['title' => 'V1', 'order' => 0]);
        $v2 = $l->videos()->create(['title' => 'V2', 'order' => 0]);
        $x = $o->videos()->create(['title' => 'X']);
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('admin.videos.reorder'), ['ids' => [$v2->id, $v1->id]])->assertOk();
        $this->assertSame([$v2->id, $v1->id], $l->videos()->pluck('id')->all());
        $this->actingAs($admin)->postJson(route('admin.videos.reorder'), ['ids' => [$v1->id, $x->id]])->assertStatus(422);
    }

    public function test_reorder_requires_admin(): void
    {
        $this->postJson(route('admin.lessons.reorder'), ['ids' => [1]])->assertUnauthorized();
        $this->actingAs(User::factory()->create())->postJson(route('admin.lessons.reorder'), ['ids' => [1]])->assertRedirect();
    }
}
