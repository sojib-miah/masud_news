<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $guarded = [];

    protected $casts = [
        'schedule' => 'date:Y-m-d',
        'expire' => 'date:Y-m-d',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_news', 'news_id', 'category_id')
            ->withTimestamps();
    }

    public function subcategories()
    {
        return $this->belongsToMany(SubCategory::class, 'news_subcategory', 'news_id', 'subcategory_id')
            ->withTimestamps();
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

    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
