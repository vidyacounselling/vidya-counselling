<?php
namespace App\Models;
class Tag extends \Illuminate\Database\Eloquent\Model {
    protected $guarded=['id'];
    public function posts() { return $this->belongsToMany(Post::class); }
}
