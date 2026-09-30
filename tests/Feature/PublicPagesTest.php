<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Contact;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        $blog = Blog::create(['title' => 'Bài 1', 'content' => 'Nội dung']);

        foreach (['/', '/bai-hoc', '/gioi-thieu', '/lien-he', '/bai-viet', '/bai-viet/'.$blog->slug] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_contact_form_stores_message(): void
    {
        $this->post('/lien-he', ['name' => 'An', 'phone' => '0900000000', 'message' => 'Xin chào'])
            ->assertRedirect(route('contact.index'));

        $this->assertSame(1, Contact::count());
    }

    public function test_video_must_belong_to_lesson(): void
    {
        $a = Lesson::create(['title' => 'A']);
        $b = Lesson::create(['title' => 'B']);
        $video = $a->videos()->create(['title' => 'V']);

        $this->get(route("courses.video", [$a, $video]))->assertOk();
        $this->get("/bai-hoc/{$b->slug}/{$video->slug}")->assertNotFound();
    }

    public function test_video_status_endpoint_returns_statuses(): void
    {
        $lesson = Lesson::create(['title' => 'L']);
        $a = $lesson->videos()->create(['title' => 'A', 'status' => 'pending']);
        $b = $lesson->videos()->create(['title' => 'B', 'status' => 'ready']);

        $this->getJson('/videos/status?ids='.$a->id.','.$b->id.',9999')
            ->assertOk()
            ->assertExactJson([(string) $a->id => 'pending', (string) $b->id => 'ready']);

        $this->getJson('/videos/status')->assertOk()->assertExactJson([]);
    }

    public function test_pending_video_page_and_course_list_are_marked_for_live_updates(): void
    {
        $lesson = Lesson::create(['title' => 'L']);
        $v = $lesson->videos()->create(['title' => 'Chờ xử lý', 'is_free' => true, 'status' => 'processing']);

        $this->get(route("courses.video", [$lesson, $v]))->assertOk()
            ->assertSee('data-vstatus-id="'.$v->id.'"', false)
            ->assertSee('id="playerShell"', false)
            ->assertSee('video-status', false);

        $this->get('/bai-hoc')->assertOk()
            ->assertSee('data-vstatus-label="'.$v->id.'"', false)
            ->assertSee('videos\\/status', false);
    }
}
