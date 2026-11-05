<?php
namespace App\Models;
class Category extends \Illuminate\Database\Eloquent\Model {
    protected $guarded=['id'];
    public function posts() { return $this->hasMany(Post::class); }
}
