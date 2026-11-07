<?php
namespace App\Services;
use App\Models\{Service,Activity};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class ServiceEditor {
 public function save(?Service $existing,array $data,int $user):Service{return DB::transaction(function()use($existing,$data,$user){
  DB::table('staff_locks')->where('id',1)->lockForUpdate()->first();$service=$existing?Service::whereKey($existing->id)->lockForUpdate()->firstOrFail():new Service;
  if($service->exists&&(int)($data['version']??0)!==$service->version)throw ValidationException::withMessages(['version'=>'Another editor saved this service. Reload and compare before saving.']);
  $sections=$data['sections']??[];unset($data['sections'],$data['version']);$slug=($data['slug']??null)?:($service->slug?:Str::slug($data['name']));if(!$slug)throw ValidationException::withMessages(['slug'=>'Enter an English URL slug.']);
  if(Service::withTrashed()->where('slug',$slug)->when($service->exists,fn($q)=>$q->where('id','!=',$service->id))->exists()||DB::table('service_slugs')->where('slug',$slug)->when($service->exists,fn($q)=>$q->where('service_id','!=',$service->id))->exists())throw ValidationException::withMessages(['slug'=>'This current or historical service URL is reserved.']);
  $old=$service->slug;$published=$service->exists&&$service->isPublic();$data['slug']=$slug;$data['body_html']=app(ArticleContent::class)->sanitize($data['body_html']??'');foreach(['offer_verified','featured','noindex'] as $key)$data[$key]=(bool)($data[$key]??false);$data['cta_label']=($data['cta_label']??null)?:'Request a Consultation';$data['updated_by']=$user;
  $service->fill($data);if($service->status==='published'){$errors=[];if(!trim($service->summary??''))$errors['summary']='Add a clear summary before publishing.';if(mb_strlen(strip_tags($service->body_html??''))<30)$errors['body_html']='Add a useful detailed description.';if(($service->fees||$service->duration)&&!$service->offer_verified)$errors['offer_verified']='Confirm the fee/duration with the centre or clear those fields.';if($service->media_id&&!trim($service->media?->alt??''))$errors['media_id']='The image needs meaningful alternative text.';if($errors)throw ValidationException::withMessages($errors);if($service->schema_mode==='manual')app(CentreSchema::class)->validateManual($service->manual_schema,$service);}
  $service->version=($service->version?:0)+1;$service->save();$service->sections()->delete();foreach($sections as $i=>$section)$service->sections()->create($section+['display_order'=>$i]);
  if($published&&$old!==$slug)DB::table('service_slugs')->updateOrInsert(['slug'=>$old],['service_id'=>$service->id,'created_at'=>now(),'updated_at'=>now()]);DB::table('service_slugs')->where('slug',$slug)->where('service_id',$service->id)->delete();Activity::record('Service saved','Service #'.$service->id);return $service;
 });}
}
