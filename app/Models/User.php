<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

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

    /**
     * Cleanup related rows when the user is force-deleted.
     *
     * Fires only on `forceDelete()` (permanent removal).
     * Regular `delete()` (soft delete) keeps all data recoverable.
     */
    protected static function booted(): void
    {
        static::forceDeleting(function (User $user) {
            DB::transaction(function () use ($user) {
                // === Pivot tables ===
                DB::table('menu_user')->where('user_id', $user->id)->delete();

                // === Reporter profile + its files ===
                if ($user->reporterProfile) {
                    foreach (['photo', 'signature', 'nid_file'] as $col) {
                        $path = $user->reporterProfile->{$col} ?? null;
                        if ($path && Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                    }
                    $user->reporterProfile()->delete();
                }

                // === Pathock profile ===
                if ($user->pathockProfile) {
                    $user->pathockProfile()->delete();
                }

                // === News views (both tables) ===
                DB::table('news_views')->where('user_id', $user->id)->delete();
                DB::table('news_user_views')->where('user_id', $user->id)->delete();

                // === Orders ===
                DB::table('orders')->where('user_id', $user->id)->delete();

                // === Stock money ===
                DB::table('stock_money')->where('user_id', $user->id)->delete();

                // === Motamots (and their images if you have an images column) ===
                if (method_exists($user, 'motamots')) {
                    foreach ($user->motamots as $motamot) {
                        if (! empty($motamot->images) && is_array($motamot->images)) {
                            foreach ($motamot->images as $img) {
                                if ($img && Storage::disk('public')->exists($img)) {
                                    Storage::disk('public')->delete($img);
                                }
                            }
                        }
                    }
                    $user->motamots()->delete();
                }

                // === News articles created by this user ===
                // `created_by` uses nullOnDelete → optionally null it out instead of deleting
                DB::table('news')->where('created_by', $user->id)->update(['created_by' => null]);
            });
        });
    }

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

    public function pathockProfile()
    {
        return $this->hasOne(PathockProfile::class, 'user_id');
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
