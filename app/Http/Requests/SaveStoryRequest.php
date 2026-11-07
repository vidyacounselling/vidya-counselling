<?php
namespace App\Http\Requests;
class SaveStoryRequest extends \Illuminate\Foundation\Http\FormRequest {
 public function authorize():bool{return $this->user()?->isAdministrator()??false;}
 public function rules():array{return ['title'=>['required','string','max:255'],'display_name'=>['nullable','string','max:120'],'anonymous'=>['required','boolean'],'quotation'=>['required','string','max:4000'],'story_html'=>['nullable','string','max:100000'],'source_type'=>['required','in:written,email,whatsapp,video,other'],'service_id'=>['nullable','exists:services,id'],'media_id'=>['nullable','exists:media,id'],'received_at'=>['nullable','date'],'featured'=>['sometimes','boolean'],'display_order'=>['required','integer','min:0','max:10000'],'status'=>['required','in:draft,published,archived'],'consent_status'=>['required','in:pending,approved,withdrawn'],'consented_at'=>['nullable','date_format:Y-m-d\TH:i'],'identity_consent'=>['sometimes','boolean'],'media_consent'=>['sometimes','boolean'],'source_consent'=>['sometimes','boolean'],'private_notes'=>['nullable','string','max:5000']];}
}
