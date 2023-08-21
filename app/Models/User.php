<?php

namespace App\Models;

use App\Models\Feeds\Feeds;
use App\Models\Trips\PaymentAccount;
use App\Models\Trips\Trips;
use App\Models\Trips\TripsJoins;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'birthdate',
        'bio',
        'links',
        'profile_photo_path',
        'phone_number',
        'otp_code',
        'password',
        'role',
        'file',
        'background_image_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function feeds()
    {
        return $this->hasMany(Feeds::class, 'user_id', 'id');
    }

    public function trips()
    {
        return $this->hasMany(Trips::class, 'user_id', 'id');
    }

    public function paymentAccount()
    {
        return $this->hasMany(PaymentAccount::class, 'user_id', 'id');
    }
}
