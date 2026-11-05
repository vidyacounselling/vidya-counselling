<?php
namespace App\Models;
class PostRevision extends \Illuminate\Database\Eloquent\Model {
    protected $guarded=['id']; protected function casts(): array { return ['snapshot'=>'array']; }
}
