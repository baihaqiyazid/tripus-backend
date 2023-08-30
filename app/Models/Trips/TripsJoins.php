<?php

namespace App\Models\Trips;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripsJoins extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feed_id'
    ];

    public function feeds()
    {
        return $this->belongsTo(Trips::class, 'feed_id', 'id');
    }
}
