<?php

namespace App\Models;

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
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::forceDeleting(function (User $user) {
            $files = [];
            if ($user->image) {
                $files[] = $user->image;
            }
            $profile = $user->reporterProfile;
            if ($profile) {
                foreach (['photo', 'image', 'signature', 'nid_file'] as $column) {
                    if ($profile->{$column}) {
                        $files[] = $profile->{$column};
                    }
                }
            }
            if (method_exists($user, 'motamots')) {
                foreach ($user->motamots as $motamot) {
                    $images = $motamot->images ?? [];
                    if (is_string($images)) {
                        $images = json_decode($images, true) ?? [];
                    }
                    if (is_array($images)) {
                        foreach ($images as $image) {
                            if (is_string($image) && $image !== '') {
                                $files[] = $image;
                            }
                        }
                    }
                }
            }
            DB::transaction(function () use ($user, $profile, $files) {
                if ($profile) {
                    $profile->delete();
                }
                $user->pathockProfile()->delete();
                foreach (
                    [
                        'menu_user',
                        'news_views',
                        'news_user_views',
                        'orders',
                        'stock_money',
                    ] as $table
                ) {
                    DB::table($table)->where('user_id', $user->id)->delete();
                }
                DB::table('model_has_roles')->where('model_id', $user->id)
                    ->where('model_type', $user->getMorphClass())->delete();
                DB::table('news')->where('created_by', $user->id)->update(['created_by' => null]);
                DB::table('news')->where('published_by', $user->id)->update(['published_by' => null]);
                $user->motamots()->delete();
            });
            foreach (array_unique($files) as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
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
                $user->email = 'user_' . Str::lower(Str::random(8)) . '@gmail.com';
            }
        });
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

    public function news()
    {
        return $this->hasMany(News::class, 'created_by');
    }
}
