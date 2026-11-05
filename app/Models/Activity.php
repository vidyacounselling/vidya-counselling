<?php
namespace App\Models;
class Activity extends \Illuminate\Database\Eloquent\Model {
    protected $guarded=['id'];
    public static function record(string $action, string $subject): void { static::create(['user_id'=>auth()->id(),'action'=>$action,'subject'=>mb_substr($subject,0,200)]); }
}
