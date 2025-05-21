<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'comment',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function post(){
        return $this->belongsTo(Post::class);
    }
    public function likes(){
        return $this->morphMany(Like::class,'likeable');
    }
}
