<?php

namespace App\Models\Categories;

use App\Models\Trips\TripsCategories;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function tripsCategory()
    {
        return $this->hasMany(TripsCategories::class, 'category_id', 'id');
    }
}
