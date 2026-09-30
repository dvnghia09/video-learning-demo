<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['slug', 'title', 'description', 'order'];
    
    public function videos()
    {
        return $this->hasMany(Video::class)->orderBy('order');
    }
}
