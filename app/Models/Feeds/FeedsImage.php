<?php

namespace App\Models\Feeds;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedsImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_url',
        'feed_id'
    ];

    public function feeds()
    {
        return $this->belongsTo(Feeds::class, 'id', 'feed_id');
    }

}
