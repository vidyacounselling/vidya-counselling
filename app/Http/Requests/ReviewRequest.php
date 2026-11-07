<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class ReviewRequest extends FormRequest {
 public function authorize():bool{return true;}
 public function rules():array{return ['display_name'=>['nullable','string','max:100'],'anonymous'=>['required','boolean'],'email'=>['required','email','max:254'],'rating'=>['nullable','integer','min:1','max:5'],'review_text'=>['required','string','min:10','max:3000'],'service_id'=>['nullable',Rule::exists('services','id')->where('status','published')->whereNull('deleted_at')],'publication_consent'=>['required','accepted'],'website'=>['nullable','max:0'],'submission_key'=>['required','uuid'],'form_signature'=>['required','string','size:64']];}
 protected function failedValidation(Validator $validator):void{throw new HttpResponseException($this->expectsJson()?response()->json(['message'=>'Please check the highlighted fields.','errors'=>$validator->errors()],422):redirect()->back()->withErrors($validator));}
}
