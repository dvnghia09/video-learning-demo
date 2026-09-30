<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessVideo implements ShouldQueue
{
    use Queueable;

    public $timeout = 3600; // 1 hour timeout for encoding

    public function __construct(public Video $video) {}

    public function handle(): void
    {
        $this->video->update(['status' => 'processing']);

        // File gốc: disk private (mới) hoặc disk public (video tải lên từ trước)
        $sourceDisk = collect(['local', 'public'])->first(fn ($d) => $this->video->video_path && Storage::disk($d)->exists($this->video->video_path));
        if (! $sourceDisk) {
            $this->fail('Không tìm thấy file gốc');

            return;
        }
        $source = Storage::disk($sourceDisk)->path($this->video->video_path);

        // HLS luôn nằm trên disk private, chỉ phát được qua route /stream có kiểm tra quyền
        $private = Storage::disk('local');
        $hlsDir = 'videos/'.$this->video->id.'_hls';
        $private->deleteDirectory($hlsDir);
        $private->makeDirectory($hlsDir);
        $outDir = $private->path($hlsDir);

        [$width, $height, $hasAudio, $duration] = $this->probe($source);
        if (! $width || ! $height) {
            $this->fail('ffprobe không đọc được video');

            return;
        }

        $short = min($width, $height);
        $landscape = $width >= $height;

        // Chỉ tạo các mức <= độ phân giải gốc (không phóng to đáng kể); luôn có ít nhất 1 mức.
        // Cho phép lệch 5% để video "1080p" bị cắt viền (cạnh ngắn 1072-1078) vẫn được đủ mức 1080p.
        $levels = array_filter(config('video.renditions'), fn ($kbps, $h) => $h <= $short * 1.05, ARRAY_FILTER_USE_BOTH);
        if (! $levels) {
            $levels = [$short => min(config('video.renditions'))];
        }

        $crf = (int) config('video.crf');

        // Bitrate theo từng video: video tĩnh được giảm bitrate, video nhiều chuyển động được tăng
        $factor = config('video.per_title') ? $this->complexityFactor($source, $duration, $landscape, $short, $crf) : 1.0;
        $levels = array_map(fn ($kbps) => (int) round($kbps * $factor), $levels);
        $split = '[0:v]split='.count($levels);
        $scales = '';
        $maps = '';
        $streamMap = [];
        $i = 0;
        foreach ($levels as $h => $kbps) {
            $split .= "[v{$i}]";
            // Giữ đúng tỉ lệ khung hình (video dọc hay ngang đều được), cạnh ngắn = $h
            $size = $landscape ? "-2:{$h}" : "{$h}:-2";
            $scales .= "[v{$i}]scale={$size}[v{$i}out];";
            $maps .= " -map [v{$i}out] -c:v:{$i} libx264 -preset veryfast -profile:v main -pix_fmt yuv420p -crf {$crf}"
                ." -maxrate:v:{$i} {$kbps}k -bufsize:v:{$i} ".($kbps * 2).'k';
            if ($hasAudio) {
                $maps .= " -map a:0 -c:a:{$i} aac -b:a:{$i} 96k -ac 2";
            }
            $streamMap[] = "v:{$i}".($hasAudio ? ",a:{$i}" : '').",name:{$h}p";
            $i++;
        }
        $filter = rtrim($split, ';').';'.rtrim($scales, ';');

        $cmd = 'ffmpeg -y -i '.escapeshellarg($source)
            .' -filter_complex '.escapeshellarg($filter)
            .$maps
            .' -g 48 -keyint_min 48 -sc_threshold 0'
            .' -f hls -hls_time 6 -hls_playlist_type vod -hls_flags independent_segments'
            .' -master_pl_name master.m3u8'
            .' -hls_segment_filename '.escapeshellarg($outDir.'/%v/seg_%03d.ts')
            .' -var_stream_map '.escapeshellarg(implode(' ', $streamMap))
            .' '.escapeshellarg($outDir.'/%v/index.m3u8').' 2>&1';

        exec($cmd, $output, $code);

        if ($code !== 0) {
            $this->fail('FFmpeg thất bại', $output);

            return;
        }

        $measured = $this->writeMeasuredBandwidth($outDir);
        Log::info('Đã mã hoá video', ['video_id' => $this->video->id, 'factor' => round($factor, 2), 'caps_kbps' => $levels, 'measured' => $measured]);

        $this->makePoster($source, $outDir.'/poster.jpg');
        $this->makeThumbnails($source, $outDir, $duration);

        $originalPath = $this->video->video_path;

        $this->video->update([
            'hls_path' => $hlsDir.'/master.m3u8',
            'status' => 'ready',
        ]);

        // Dọn bản HLS cũ nằm ở disk public (nếu có) — nó có thể bị tải trực tiếp không cần quyền
        Storage::disk('public')->deleteDirectory($hlsDir);

        // Xoá file gốc để giải phóng dung lượng
        if (config('video.delete_original')) {
            foreach (['local', 'public'] as $d) {
                Storage::disk($d)->delete($originalPath);
            }
            $this->video->update(['video_path' => null]);
        }
    }

    /**
     * Đo độ phức tạp: mã hoá thử vài đoạn ngắn ở 480p (hoặc nhỏ hơn) với CRF hiện tại, không giới hạn bitrate,
     * rồi so bitrate đo được với mức tham chiếu. >1 = video nhiều chuyển động, <1 = video tĩnh.
     */
    private function complexityFactor(string $source, float $duration, bool $landscape, int $short, int $crf): float
    {
        $clip = 6.0;
        $starts = $duration <= 30 ? [0.0] : [$duration * 0.2, $duration * 0.5, $duration * 0.8];
        $len = $duration <= 30 ? $duration : $clip;
        $probeH = min(480, $short);
        $scale = $landscape ? "-2:{$probeH}" : "{$probeH}:-2";

        $bytes = 0;
        $seconds = 0.0;
        foreach ($starts as $ss) {
            $tmp = tempnam(sys_get_temp_dir(), 'probe').'.mp4';
            exec('ffmpeg -y -ss '.round($ss, 2).' -t '.round($len, 2).' -i '.escapeshellarg($source)
                .' -an -vf scale='.$scale.' -c:v libx264 -preset veryfast -crf '.$crf.' -pix_fmt yuv420p '.escapeshellarg($tmp).' 2>&1', $o, $code);
            if ($code === 0 && is_file($tmp)) {
                $bytes += filesize($tmp);
                $seconds += $len;
            }
            @unlink($tmp);
        }
        if ($seconds <= 0) {
            return 1.0;
        }

        // Quy về 480p để so với mức tham chiếu (bitrate tỉ lệ gần đúng với số điểm ảnh^0.75)
        $kbps = $bytes * 8 / 1000 / $seconds;
        $kbps480 = $kbps * (pow(480 / $probeH, 1.5));

        return max((float) config('video.factor_min'), min((float) config('video.factor_max'), $kbps480 / config('video.reference_kbps')));
    }

    /**
     * Ghi BANDWIDTH (đỉnh) và AVERAGE-BANDWIDTH (trung bình) đo từ file thật vào master.m3u8,
     * để player ước lượng đúng khi chọn mức chất lượng thay vì dùng bitrate trần.
     *
     * @return array<string, array{avg_kbps:int, peak_kbps:int}>
     */
    private function writeMeasuredBandwidth(string $outDir): array
    {
        $masterFile = $outDir.'/master.m3u8';
        $lines = file($masterFile, FILE_IGNORE_NEW_LINES);
        $report = [];

        foreach ($lines as $i => $line) {
            if (! str_starts_with($line, '#EXT-X-STREAM-INF') || ! isset($lines[$i + 1])) {
                continue;
            }
            $rel = trim($lines[$i + 1]);
            $dir = dirname($rel);
            $index = @file($outDir.'/'.$rel, FILE_IGNORE_NEW_LINES);
            if (! $index) {
                continue;
            }

            $total = 0;
            $totalDur = 0.0;
            $peak = 0;
            $dur = 0.0;
            foreach ($index as $l) {
                if (str_starts_with($l, '#EXTINF:')) {
                    $dur = (float) substr($l, 8);
                } elseif ($l !== '' && $l[0] !== '#' && is_file($outDir.'/'.$dir.'/'.$l)) {
                    $size = filesize($outDir.'/'.$dir.'/'.$l);
                    $total += $size;
                    $totalDur += $dur;
                    $peak = max($peak, $dur > 0 ? (int) ($size * 8 / $dur) : 0);
                }
            }
            if ($totalDur <= 0) {
                continue;
            }

            $avg = (int) ($total * 8 / $totalDur);
            $lines[$i] = preg_replace('/BANDWIDTH=\d+/', "BANDWIDTH={$peak},AVERAGE-BANDWIDTH={$avg}", $line, 1);
            $report[$dir] = ['avg_kbps' => intdiv($avg, 1000), 'peak_kbps' => intdiv($peak, 1000)];
        }

        file_put_contents($masterFile, implode("\n", $lines)."\n");

        return $report;
    }

    /** @return array{0:int,1:int,2:bool,3:float} width, height, có âm thanh, thời lượng (giây) */
    private function probe(string $source): array
    {
        exec('ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0:s=x '.escapeshellarg($source), $v);
        exec('ffprobe -v error -select_streams a -show_entries stream=index -of csv=p=0 '.escapeshellarg($source), $a);
        exec('ffprobe -v error -show_entries format=duration -of csv=p=0 '.escapeshellarg($source), $d);

        [$w, $h] = array_pad(array_map('intval', explode('x', $v[0] ?? '')), 2, 0);

        return [$w, $h, ! empty($a), (float) ($d[0] ?? 0)];
    }

    private function makePoster(string $source, string $target): void
    {
        // Lấy khung hình ở giây thứ 1 (hoặc đầu video nếu video ngắn) làm ảnh bìa
        foreach (['1', '0'] as $ss) {
            exec('ffmpeg -y -ss '.$ss.' -i '.escapeshellarg($source).' -frames:v 1 -q:v 3 '.escapeshellarg($target).' 2>&1', $o, $code);
            if ($code === 0 && is_file($target)) {
                return;
            }
        }
    }

    /** Tạo ảnh ghép (sprite) + file VTT để hiện ảnh xem trước khi rê chuột trên thanh tua. */
    private function makeThumbnails(string $source, string $outDir, float $duration): void
    {
        if ($duration <= 0) {
            return;
        }

        $tw = 160;
        $th = 90;
        $cols = 10;
        $interval = max(2, (int) ceil($duration / config('video.thumbs_max')));
        $count = (int) ceil($duration / $interval);
        $rows = (int) ceil($count / $cols);

        $vf = "fps=1/{$interval},scale={$tw}:{$th}:force_original_aspect_ratio=decrease,pad={$tw}:{$th}:(ow-iw)/2:(oh-ih)/2:black,tile={$cols}x{$rows}";
        exec('ffmpeg -y -i '.escapeshellarg($source).' -vf '.escapeshellarg($vf).' -frames:v 1 -q:v 6 '.escapeshellarg($outDir.'/thumbs.jpg').' 2>&1', $o, $code);
        if ($code !== 0 || ! is_file($outDir.'/thumbs.jpg')) {
            return;
        }

        $fmt = fn (float $t) => sprintf('%02d:%02d:%06.3f', intdiv((int) $t, 3600), intdiv((int) $t % 3600, 60), fmod($t, 60));
        $vtt = "WEBVTT\n\n";
        for ($n = 0; $n < $count; $n++) {
            $start = $n * $interval;
            $end = min(($n + 1) * $interval, $duration);
            $x = ($n % $cols) * $tw;
            $y = intdiv($n, $cols) * $th;
            $vtt .= $fmt($start).' --> '.$fmt($end)."\nthumbs.jpg#xywh={$x},{$y},{$tw},{$th}\n\n";
        }
        file_put_contents($outDir.'/thumbs.vtt', $vtt);
    }

    private function fail(string $reason, array $output = []): void
    {
        $this->video->update(['status' => 'failed']);
        Log::error($reason, ['video_id' => $this->video->id, 'output' => array_slice($output, -20)]);
    }
}
