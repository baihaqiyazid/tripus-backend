<?php

namespace App\Models\Trips;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trips extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'location',
        'fee',
        'limit',
        'date_from',
        'date_end',
        'transportation',
        'guide',
        'porter',
        'eat',
        'breakfast',
        'lunch',
        'permit',
        'others',
        'exclude'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    public function tripsImages()
    {
        return $this->hasMany(TripsImages::class, 'trip_id', 'id');
    }

    public function tripsLikes()
    {
        return $this->hasMany(TripsLikes::class, 'trip_id', 'id');
    }

    public function tripsSaves()
    {
        return $this->hasMany(TripsSaves::class, 'trip_id', 'id');
    }

    public function tripsCategories()
    {
        return $this->hasMany(TripsCategories::class, 'trip_id', 'id');
    }

    public function paymentAccounts()
    {
        return $this->hasMany(PaymentAccount::class, 'trip_id', 'id');
    }

    public function tripsJoins()
    {
        return $this->hasMany(TripsJoins::class, 'trip_id', 'id');
    }
}
