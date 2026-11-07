<?php
namespace App\Models;
use Illuminate\Database\Eloquent\{Model,SoftDeletes};
class Service extends Model {
 use SoftDeletes; protected $guarded=['id'];protected $attributes=['version'=>1,'status'=>'draft','schema_mode'=>'automatic'];
 protected function casts():array{return ['featured'=>'boolean','offer_verified'=>'boolean','noindex'=>'boolean','version'=>'integer'];}
 public function media(){return $this->belongsTo(Media::class);}public function sections(){return $this->hasMany(ServiceSection::class)->orderBy('display_order')->orderBy('id');}
 public function scopeEligible($q){return $q->where('status','published');}public function isPublic():bool{return !$this->trashed()&&$this->status==='published';}
 public function getPublicUrlAttribute():string{return url('/services/'.$this->slug);}public function getCanonicalAttribute():string{return $this->canonical_url?:$this->public_url;}
 public function facts(string $kind){return $this->sections->where('kind',$kind);}
}
