<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterContentOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'content_type',
        'content_id',
        'order',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
