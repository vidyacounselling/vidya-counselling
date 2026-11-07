<?php
namespace App\Models;
class Inquiry extends \Illuminate\Database\Eloquent\Model {
 use \Illuminate\Database\Eloquent\SoftDeletes;protected $guarded=['id'];protected $hidden=['full_name','email','mobile','message','preferred_window','duplicate_key','submission_key'];
 protected function casts():array{return ['full_name'=>'encrypted','email'=>'encrypted','mobile'=>'encrypted','message'=>'encrypted','preferred_window'=>'encrypted','consented_at'=>'datetime','contacted_at'=>'datetime','closed_at'=>'datetime','redacted_at'=>'datetime'];}
 public function service(){return $this->belongsTo(Service::class)->withTrashed();}public function assignee(){return $this->belongsTo(User::class,'assigned_to')->withTrashed();}public function followups(){return $this->hasMany(InquiryFollowup::class)->latest();}
}
