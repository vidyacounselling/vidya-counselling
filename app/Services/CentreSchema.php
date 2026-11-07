<?php
namespace App\Services;
use App\Models\Service;
use Illuminate\Validation\ValidationException;
class CentreSchema {
 public function organization():array{$s=app(WebsiteSettings::class)->all();$data=['@context'=>'https://schema.org','@type'=>'Organization','@id'=>url('/').'#organization','name'=>$s['brand_name'],'url'=>url('/'),'logo'=>asset('images/vidya-logo.png')];if(($s['contact_verified']??'0')==='1')foreach(['phone'=>'telephone','email'=>'email','address'=>'address','service_area'=>'areaServed'] as $key=>$property)if(!empty($s[$key]))$data[$property]=$s[$key];$same=array_values(array_filter([$s['instagram']??null,$s['facebook']??null]));if($same)$data['sameAs']=$same;return $data;}
 public function validateManual(?string $text,Service $service):?array {
  if(!trim($text??''))return null;
  try{$data=json_decode($text,true,32,JSON_THROW_ON_ERROR);}catch(\Throwable){$this->fail('Enter valid JSON data, without script tags.');}
  if(!is_array($data)||strlen($text)>40000)$this->fail('Use JSON object/array/@graph under 40 KB.');$nodes=$data['@graph']??(array_is_list($data)?$data:[$data]);$services=0;$breadcrumbs=0;$ids=[];
  foreach($nodes as $node){if(!is_array($node))$this->fail('Each node must be a JSON object.');$types=(array)($node['@type']??[]);if(array_intersect($types,['Review','AggregateRating','Physician','Hospital','MedicalClinic','FAQPage']))$this->fail('Unsupported review, medical or duplicate FAQ schema node.');if(isset($node['@context'])&&!in_array($node['@context'],['https://schema.org','https://schema.org/'],true))$this->fail('Use the schema.org HTTPS context.');if(in_array('Service',$types,true)){$services++;if(($node['name']??null)!==$service->name||($node['url']??null)!==$service->canonical)$this->fail('Service name and URL must match this visible service.');}if(in_array('BreadcrumbList',$types,true))$breadcrumbs++;if(isset($node['@id'])){if(in_array($node['@id'],$ids,true))$this->fail('Duplicate @id values are not allowed.');$ids[]=$node['@id'];}}
  if($services!==1||$breadcrumbs>1)$this->fail('Provide one Service node and at most one BreadcrumbList.');return $data;
 }
 private function fail(string $message):never{throw ValidationException::withMessages(['manual_schema'=>$message]);}
 public function service(Service $service):array {
  $breadcrumb=$this->breadcrumbs([['Home',url('/')],['Services',url('/services')],[$service->name,$service->canonical]]);
  $manual=$service->schema_mode==='manual'?$this->validateManual($service->manual_schema,$service):null;
  if($manual){$nodes=$manual['@graph']??(array_is_list($manual)?$manual:[$manual]);$has=false;foreach($nodes as $node)if(in_array('BreadcrumbList',(array)($node['@type']??[]),true))$has=true;return ['source'=>'Manual','data'=>['@context'=>'https://schema.org','@graph'=>$has?$nodes:[...$nodes,$breadcrumb]]];}
  $org=$this->organization();$data=['@type'=>'Service','@id'=>$service->canonical.'#service','name'=>$service->name,'description'=>$service->summary,'url'=>$service->canonical,'provider'=>['@id'=>$org['@id']],'serviceType'=>$service->name];
  $modes=$service->facts('modes')->pluck('content');if($modes->isNotEmpty())$data['availableChannel']=$modes->map(fn($mode)=>['@type'=>'ServiceChannel','name'=>$mode,'serviceUrl'=>url('/contact?service='.$service->id)])->all();
  $audience=$service->facts('audience')->pluck('content');if($audience->isNotEmpty())$data['audience']=$audience->map(fn($value)=>['@type'=>'Audience','audienceType'=>$value])->all();if(isset($org['areaServed']))$data['areaServed']=$org['areaServed'];if($service->media)$data['image']=$service->media->url;
  return ['source'=>'Automatic','data'=>['@context'=>'https://schema.org','@graph'=>[$data,$breadcrumb]]];
 }
 public function breadcrumbs(array $items):array{return ['@type'=>'BreadcrumbList','itemListElement'=>array_map(fn($item,$i)=>['@type'=>'ListItem','position'=>$i+1,'name'=>$item[0],'item'=>$item[1]],$items,array_keys($items))];}
 public function encode(array $data):string{return json_encode($data,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);}
}
