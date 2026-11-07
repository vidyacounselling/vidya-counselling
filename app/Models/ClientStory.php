<?php
namespace App\Models;
class ClientStory extends \Illuminate\Database\Eloquent\Model {
 use \Illuminate\Database\Eloquent\SoftDeletes;protected $guarded=['id'];protected $hidden=['private_notes','original_quotation'];
 protected function casts():array{return ['anonymous'=>'boolean','identity_consent'=>'boolean','media_consent'=>'boolean','source_consent'=>'boolean','featured'=>'boolean','consented_at'=>'datetime','received_at'=>'date','private_notes'=>'encrypted'];}
 public function media(){return $this->belongsTo(Media::class);}public function service(){return $this->belongsTo(Service::class);}
 public function scopeEligible($q){return $q->where('status','published')->where('consent_status','approved')->whereNotNull('consented_at');}
 public function getPublicNameAttribute():string{return $this->anonymous||!$this->identity_consent?'Anonymous client':($this->display_name?:'Anonymous client');}
}
