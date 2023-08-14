<?php

namespace App\Models\Trips;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripsImages extends Model
{
    use HasFactory;
    protected $fillable = [
        'image_url',
        'trip_id'
    ];

    public function trips()
    {
        return $this->belongsTo(Trips::class, 'id', 'trip_id');
    }

}
