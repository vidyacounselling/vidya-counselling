<?php
namespace App\Models;
class Media extends \Illuminate\Database\Eloquent\Model {
    protected $table='media'; protected $guarded=['id'];
    public function getUrlAttribute(): string { return url('/media/'.$this->filename); }
    public function getSrcsetAttribute(): string { $parts=[];foreach([640,1200] as $size)if($this->width>$size)$parts[]=$this->url.'?w='.$size.' '.$size.'w';$parts[]=$this->url.' '.$this->width.'w';return implode(', ',$parts); }
}
