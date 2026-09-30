<?php

namespace App\Console\Commands;

use App\Jobs\ProcessVideo;
use App\Models\Video;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('videos:reprocess {id?* : ID video cần mã hoá lại (bỏ trống = tất cả video còn file gốc)}')]
#[Description('Mã hoá lại video sang HLS nhiều chất lượng')]
class ReprocessVideos extends Command
{
    public function handle(): int
    {
        $videos = Video::query()
            ->when($this->argument('id'), fn ($q, $ids) => $q->whereIn('id', $ids))
            ->whereNotNull('video_path')
            ->get();

        foreach ($videos as $video) {
            $video->update(['status' => 'pending']);
            ProcessVideo::dispatch($video);
            $this->line("Đã xếp hàng video #{$video->id}: {$video->title}");
        }

        $this->info("Xong: {$videos->count()} video được xếp hàng xử lý.");

        return self::SUCCESS;
    }
}
