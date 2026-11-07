<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class InquiryRequest extends FormRequest {
 public function authorize():bool{return true;}
 protected function prepareForValidation():void{$phone=preg_replace('/[\s().-]+/','',(string)$this->mobile);$code=preg_replace('/\s/','',(string)$this->input('country_code',''));if($code&&!str_starts_with($phone,'+'))$phone=$code.$phone;$this->merge(['mobile'=>$phone,'email'=>mb_strtolower(trim((string)$this->email))]);}
 public function rules():array{return ['full_name'=>['required','string','min:2','max:120'],'email'=>['required','email','max:254'],'mobile'=>['required','regex:/^\+?[0-9]{7,15}$/'],'interest'=>['required',Rule::in(['Mental Health','Marriage and Family Counseling'])],'session_preference'=>['required',Rule::in(['In-person','Online','No preference / Help me decide'])],'service_id'=>['nullable',Rule::exists('services','id')->where('status','published')->whereNull('deleted_at')],'contact_method'=>['required','in:phone,whatsapp,email'],'preferred_window'=>['nullable','string','max:200'],'message'=>['nullable','string','max:1500'],'consent'=>['required','accepted'],'website'=>['nullable','max:0'],'submission_key'=>['required','uuid'],'form_signature'=>['required','string','size:64'],'source'=>['required','in:consultation_form,lets_talk']];}
 protected function failedValidation(Validator $validator):void{throw new HttpResponseException($this->expectsJson()?response()->json(['message'=>'Please check the highlighted fields.','errors'=>$validator->errors()],422):redirect()->back()->withErrors($validator));}
}
