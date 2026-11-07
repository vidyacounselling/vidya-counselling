<?php
namespace App\Models;
class InquiryFollowup extends \Illuminate\Database\Eloquent\Model {protected $guarded=['id'];protected $hidden=['note'];protected function casts():array{return ['note'=>'encrypted'];}public function user(){return $this->belongsTo(User::class)->withTrashed();}}
