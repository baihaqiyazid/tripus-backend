<?php

namespace App\Models\Trips;

use App\Models\Payment\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'payment_method_id',
        'user_id', 
        'number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'id', 'payment_method_id');
    }
}
