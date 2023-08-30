<?php

namespace App\Models\Feeds;

use App\Models\approval_letters;
use App\Models\cancel_trip;
use App\Models\RequestApprovalLetters;
use App\Models\RequestCancelTrips;
use App\Models\RequestWithdrawTrips;
use App\Models\Trips\TripsJoins;
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
        'meeting_point',
        'title',
        'include',
        'exclude',
        'others',
        'category_id',
        'date_start',
        'date_end',
        'fee',
        'max_person',
        'payment_account',
        'type'
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

    public function feedsJoin()
    {
        return $this->hasMany(TripsJoins::class, 'feed_id', 'id');
    }

    public function cancelTrip()
    {
        return $this->hasMany(RequestCancelTrips::class, 'feed_id', 'id');
    }

    public function withdrawTrip()
    {
        return $this->hasMany(RequestWithdrawTrips::class, 'feed_id', 'id');
    }
}
