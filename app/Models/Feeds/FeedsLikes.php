<?php

namespace App\Models\Feeds;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedsLikes extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feed_id'
    ];

    public function feed()
    {
        return $this->belongsTo(Feeds::class, 'feed_id', 'id');
    }
}
