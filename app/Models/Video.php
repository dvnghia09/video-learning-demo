<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['lesson_id', 'title', 'slug', 'video_path', 'hls_path', 'is_free', 'order', 'status'];

    protected function casts(): array
    {
        return ['is_free' => 'boolean'];
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /** Video miễn phí, hoặc người xem là VIP / quản trị viên. */
    public function canBeWatchedBy(?User $user): bool
    {
        return $this->is_free || ($user && ($user->is_vip || $user->role === 'admin'));
    }

    /** Ảnh thu nhỏ (khung hình đầu do hệ thống tạo khi mã hoá). Chỉ có khi video đã xử lý xong. */
    public function posterUrl(): ?string
    {
        if ($this->status !== 'ready' || ! $this->hls_path) {
            return null;
        }

        return route('stream', ['video' => $this->id, 'path' => 'poster.jpg', 'v' => $this->updated_at?->timestamp]);
    }

    /** Thư mục HLS (trên disk private "local"), ví dụ videos/5_hls */
    public function hlsDirectory(): ?string
    {
        return $this->hls_path ? dirname($this->hls_path) : null;
    }
}
