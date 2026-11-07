<?php
namespace App\Models;
class Faq extends \Illuminate\Database\Eloquent\Model {
 use \Illuminate\Database\Eloquent\SoftDeletes;protected $guarded=['id'];
 public function pages(){return $this->belongsToMany(PublicPage::class,'faq_page')->withPivot('display_order');}
}
