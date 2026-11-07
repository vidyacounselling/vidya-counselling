<?php
namespace App\Models;
class Review extends \Illuminate\Database\Eloquent\Model {
 use \Illuminate\Database\Eloquent\SoftDeletes;protected $guarded=['id'];protected $hidden=['email','moderation_notes','duplicate_key','submission_key'];
 protected function casts():array{return ['anonymous'=>'boolean','publication_consent'=>'boolean','featured'=>'boolean','email'=>'encrypted','moderation_notes'=>'encrypted','consented_at'=>'datetime'];}
 public function service(){return $this->belongsTo(Service::class);}
 public function scopeEligible($q){return $q->where('status','approved')->where('publication_consent',true)->whereNotNull('consented_at');}
 public function getPublicNameAttribute():string{return $this->anonymous?'Anonymous reviewer':($this->display_name?:'Anonymous reviewer');}
 public function getTextAttribute():string{return $this->public_text?:$this->original_text;}
}
