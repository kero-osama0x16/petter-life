<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['author_id', 'title', 'slug', 'content', 'image_path', 'category'];
    
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}


