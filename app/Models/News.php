<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $guarded = [];

    // protected $casts = [
    //     'featured' => 'boolean',
    //     'breaking' => 'boolean',
    //     'status' => 'boolean',
    // ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function views()
    {
        return $this->hasMany(NewsView::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
