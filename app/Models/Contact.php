<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    public const STATUSES = [
        'new' => 'Mới',
        'read' => 'Đã xem',
        'handled' => 'Đã liên hệ lại',
    ];

    protected $fillable = ['name', 'phone', 'message', 'status'];

    /** Nhãn tiếng Việt của trạng thái (kể cả giá trị cũ/lạ). */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? 'Không rõ';
    }
}
