<?php
namespace App\Http\Requests;
class SaveFaqRequest extends \Illuminate\Foundation\Http\FormRequest {
 public function authorize():bool{return $this->user()?->isStaff()??false;}
 public function rules():array{return ['question'=>['required','string','max:500'],'answer_html'=>['nullable','string','max:15000'],'category'=>['nullable','string','max:100'],'status'=>['required','in:draft,published'],'editorial_notes'=>['nullable','string','max:3000'],'pages'=>['nullable','array','max:500'],'pages.*'=>['integer','distinct','exists:public_pages,id'],'orders'=>['nullable','array','max:500'],'orders.*'=>['integer','min:0','max:10000'],'schema_page_id'=>['nullable','exists:public_pages,id']];}
}
