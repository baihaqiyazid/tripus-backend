<?php

namespace App\Models\Trips;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripsJoins extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trip_id'
    ];

    public function trips()
    {
        return $this->belongsTo(Trips::class, 'trip_id', 'id');
    }
}
