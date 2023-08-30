<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestWithdrawTrips extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function feed()
    {
        return $this->belongsTo(Feeds::class, 'feed_id', 'id');
    }
}
