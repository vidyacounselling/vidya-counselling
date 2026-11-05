<?php
namespace App\Models;
class Comment extends \Illuminate\Database\Eloquent\Model {
    protected $guarded=['id']; protected $hidden=['email'];
    public function post() { return $this->belongsTo(Post::class); }
}
