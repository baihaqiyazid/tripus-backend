<?php

namespace App\Models\Payment;

use App\Models\Trips\PaymentAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location'
    ];

    public function paymentAccount()
    {
        return $this->hasMany(PaymentAccount::class, 'payment_method_id', 'id');
    }
}
