<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'profile_id',
        'post_image',
        'caption',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

}
