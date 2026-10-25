<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class);
    }

    public function billings()
    {
        return $this->hasMany(Billing::class, 'customer_id');
    }

    public function reporterProfile()
    {
        return $this->hasOne(ReporterProfile::class);
    }
    public function reporterTypes()
    {
        return $this->belongsToMany(ReporterType::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {

            // ref id
            $user->ref_id = rand(10000000, 99999999);

            // random email
            if (empty($user->email)) {

                $user->email =
                    'user_' .
                    Str::lower(Str::random(8)) .
                    '@gmail.com';
            }
        });

        static::deleting(
            function ($user) {
                if ($user->role === 'reporter') {
                    $user->reporterProfile()?->delete();
                }
            }
        );
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function motamots()
    {
        return $this->hasMany(Motamot::class);
    }
}
