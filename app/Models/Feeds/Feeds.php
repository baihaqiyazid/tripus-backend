<?php

namespace App\Models\Feeds;

use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feeds extends Model
{
    use HasFactory;

    protected function serializeDate(\DateTimeInterface $date)
    {
        return Carbon::instance($date)->toIso8601String();
    }

    protected $fillable = [
        'description',
        'location',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function feedImage()
    {
        return $this->hasMany(FeedsImage::class, 'feed_id', 'id');
    }

    public function feedsLikes()
    {
        return $this->hasMany(FeedsLikes::class, 'feed_id', 'id');
    }

    public function feedsSaves()
    {
        return $this->hasMany(FeedsSaves::class, 'feed_id', 'id');
    }
}
