<?php
namespace App\Services;
use App\Models\{Inquiry,Review};
use Illuminate\Http\Request;
use Illuminate\Support\{Str,Facades\DB};
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
class PublicSubmissions {
 public function token(Request $r):array{$id=(string)Str::uuid();return ['submission_key'=>$id,'form_signature'=>hash_hmac('sha256',$id.'|'.$r->session()->getId(),config('app.key'))];}
 private function verify(Request $r,array $data):void{$expected=hash_hmac('sha256',$data['submission_key'].'|'.$r->session()->getId(),config('app.key'));abort_unless(hash_equals($expected,$data['form_signature']),419);}
 private function duplicate(string $value):string{return hash_hmac('sha256',$value.'|'.now()->toDateString(),config('app.key'));}
 public function inquiry(Request $r,array $data):Inquiry {
  $this->verify($r,$data);$existing=Inquiry::withTrashed()->where('submission_key',$data['submission_key'])->first();if($existing)return $existing;
  $key=$this->duplicate(mb_strtolower($data['email']).'|'.$data['mobile'].'|'.trim($data['message']??'').'|'.$data['source']);
  $existing=Inquiry::withTrashed()->where('duplicate_key',$key)->first();if($existing)return $existing;
  try{return Inquiry::create(['reference'=>'VCC-'.Str::upper(Str::random(16)),'submission_key'=>$data['submission_key'],'duplicate_key'=>$key,'full_name'=>$data['full_name'],'email'=>$data['email'],'mobile'=>$data['mobile'],'interest'=>$data['interest'],'session_preference'=>$data['session_preference'],'service_id'=>$data['service_id']??null,'contact_method'=>$data['contact_method'],'preferred_window'=>$data['preferred_window']??null,'message'=>$data['message']??null,'source'=>$data['source'],'status'=>'new','consent_version'=>app(WebsiteSettings::class)->get('consent_version','consultation-20261107'),'consented_at'=>now()]);}
  catch(UniqueConstraintViolationException $e){return Inquiry::withTrashed()->where('submission_key',$data['submission_key'])->orWhere('duplicate_key',$key)->firstOrFail();}
 }
 public function review(Request $r,array $data,string $source='website'):Review {
  $this->verify($r,$data);if($data['review_text']!==strip_tags($data['review_text']))throw ValidationException::withMessages(['review_text'=>'Use plain text without HTML.']);if(!$data['anonymous']&&!trim($data['display_name']??''))throw ValidationException::withMessages(['display_name'=>'Provide a display name or choose anonymous.']);
  $key=$this->duplicate(mb_strtolower($data['email']).'|'.trim($data['review_text']));$existing=Review::withTrashed()->where('submission_key',$data['submission_key'])->orWhere('duplicate_key',$key)->first();if($existing)return $existing;
  try{return Review::create(['submission_key'=>$data['submission_key'],'duplicate_key'=>$key,'display_name'=>$data['display_name']??null,'anonymous'=>$data['anonymous'],'email'=>mb_strtolower($data['email']),'rating'=>$data['rating']??null,'original_text'=>$data['review_text'],'service_id'=>$data['service_id']??null,'source'=>$source,'publication_consent'=>true,'consented_at'=>now(),'consent_version'=>app(WebsiteSettings::class)->get('review_consent_version','review-20261107'),'status'=>'pending']);}
  catch(UniqueConstraintViolationException $e){return Review::withTrashed()->where('duplicate_key',$key)->orWhere('submission_key',$data['submission_key'])->firstOrFail();}
 }
}
