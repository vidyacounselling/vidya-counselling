<?php
namespace App\Models;
class Media extends \Illuminate\Database\Eloquent\Model {
    protected $table='media'; protected $guarded=['id'];
    public function getUrlAttribute(): string { return url('/media/'.$this->filename); }
}
