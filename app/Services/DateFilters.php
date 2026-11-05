<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
class DateFilters {
 public static function apply($query,Request $request,string $column): void {
    foreach(['from'=>'>=','to'=>'<='] as $key=>$operator) {
        $value=$request->query($key);
        if(!is_string($value)||!validator([$key=>$value],[$key=>'date_format:Y-m-d'])->passes())continue;
        $date=Carbon::createFromFormat('!Y-m-d',$value,'Asia/Kolkata');
        $query->where($column,$operator,($key==='from'?$date->startOfDay():$date->endOfDay())->utc());
    }
 }
}
