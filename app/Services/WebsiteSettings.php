<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class WebsiteSettings {
 public const KEYS=['brand_name','founder_name','founder_intro','story_html','hero_title','hero_description','phone','email','whatsapp','contact_verified','whatsapp_verified','address','map_url','service_area','opening_hours','instagram','facebook','default_description','founder_media_id','logo_media_id','default_social_image','faq_schema_enabled','inquiry_retention_days','consent_copy','consent_version','review_consent_version'];
 public function all():array {return DB::table('site_settings')->whereIn('key',self::KEYS)->pluck('value','key')->all()+['brand_name'=>'Vidya Counseling Centre'];}
 public function get(string $key,string $default=''):string{return (string)($this->all()[$key]??$default);}
 public function save(array $data):void{DB::transaction(function()use($data){foreach(array_intersect_key($data,array_flip(self::KEYS)) as $key=>$value)DB::table('site_settings')->updateOrInsert(['key'=>$key],['value'=>(string)($value??''),'updated_at'=>now()]);});}
}
