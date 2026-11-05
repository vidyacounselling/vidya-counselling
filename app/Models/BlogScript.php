<?php
namespace App\Models;
class BlogScript extends \Illuminate\Database\Eloquent\Model {
    protected $guarded=['id']; protected function casts(): array { return ['enabled'=>'boolean','activated_at'=>'datetime']; }
}
