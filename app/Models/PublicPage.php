<?php
namespace App\Models;
class PublicPage extends \Illuminate\Database\Eloquent\Model {
 protected $guarded=['id'];
 public function faqs(){return $this->belongsToMany(Faq::class,'faq_page')->withPivot('display_order')->orderByPivot('display_order');}
 public function eligible():bool{return match($this->kind){'service'=>Service::find($this->entity_id)?->isPublic()??false,'post'=>Post::find($this->entity_id)?->isPublic()??false,default=>$this->status==='published'};}
}
