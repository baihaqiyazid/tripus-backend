<?php

namespace App\Models\Trips;

use App\Models\Payment\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'payment_method_id',
        'trip_id', 
        'number'
    ];

    public function trips()
    {
        return $this->belongsTo(Trips::class, 'id', 'trip_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'id', 'payment_method_id');
    }
}
