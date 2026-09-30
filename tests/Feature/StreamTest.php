<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StreamTest extends TestCase
{
    use RefreshDatabase;

    private function video(bool $free): Video
    {
        Storage::fake('local');
        $lesson = Lesson::create(['title' => 'L']);
        $video = $lesson->videos()->create(['title' => 'V', 'is_free' => $free, 'status' => 'ready', 'hls_path' => 'videos/1_hls/master.m3u8']);
        $dir = "videos/{$video->id}_hls";
        $video->update(['hls_path' => "$dir/master.m3u8"]);
        foreach (['master.m3u8', '720p/index.m3u8', '720p/seg_000.ts', 'poster.jpg', 'thumbs.vtt'] as $f) {
            Storage::disk('local')->put("$dir/$f", 'data');
        }

        return $video;
    }

    public function test_free_video_streams_for_guests_with_correct_types(): void
    {
        $v = $this->video(true);

        $this->get("/stream/{$v->id}/master.m3u8")->assertOk()->assertHeader('Content-Type', 'application/vnd.apple.mpegurl');
        $this->get("/stream/{$v->id}/720p/seg_000.ts")->assertOk()->assertHeader('Content-Type', 'video/mp2t');
    }

    public function test_locked_video_needs_vip_or_admin_but_poster_is_public(): void
    {
        $v = $this->video(false);

        $this->get("/stream/{$v->id}/master.m3u8")->assertForbidden();
        $this->get("/stream/{$v->id}/720p/seg_000.ts")->assertForbidden();
        $this->get("/stream/{$v->id}/poster.jpg")->assertOk();
        $this->get("/stream/{$v->id}/thumbs.vtt")->assertOk();

        $this->actingAs(User::factory()->create())->get("/stream/{$v->id}/master.m3u8")->assertForbidden();
        $this->actingAs(User::factory()->create(['is_vip' => true]))->get("/stream/{$v->id}/master.m3u8")->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get("/stream/{$v->id}/720p/seg_000.ts")->assertOk();
    }

    public function test_only_known_file_names_are_served(): void
    {
        $v = $this->video(true);

        foreach (['../../../.env', '720p/../../secret.txt', 'evil.php', '720p/seg_000.ts.bak', '%2e%2e/master.m3u8'] as $bad) {
            $this->get("/stream/{$v->id}/$bad")->assertNotFound();
        }
    }

    public function test_supports_range_requests(): void
    {
        $v = $this->video(true);

        $this->get("/stream/{$v->id}/720p/seg_000.ts", ['Range' => 'bytes=0-1'])->assertStatus(206);
    }
}
