<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoProgress extends Model
{
    protected $table = 'video_progress';

    protected $fillable = ['user_id', 'video_id', 'position', 'duration', 'completed'];

    protected function casts(): array
    {
        return ['completed' => 'boolean'];
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    /** Phần trăm đã xem (0–100). */
    public function percent(): int
    {
        if ($this->completed) {
            return 100;
        }

        return $this->duration > 0 ? (int) min(100, round($this->position * 100 / $this->duration)) : 0;
    }
}
