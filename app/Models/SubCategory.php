<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $table = 'sub_categories';

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function news()
    {
        return $this->hasMany(News::class, 'subcategory_id', 'id');
    }

    public function newsMany()
    {
        return $this->belongsToMany(News::class, 'news_subcategory');
    }
}
