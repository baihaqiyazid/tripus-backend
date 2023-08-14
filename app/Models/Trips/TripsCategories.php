<?php

namespace App\Models\Trips;

use App\Models\Categories\Categories;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripsCategories extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'trip_id'
    ];

    public function trips()
    {
        return $this->belongsTo(Trips::class, 'trip_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id', 'id');
    }
}
