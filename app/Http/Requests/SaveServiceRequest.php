<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class SaveServiceRequest extends FormRequest {
 public function authorize():bool{return $this->user()?->isStaff()??false;}
 public function rules():array{return [
 'name'=>['required','string','max:180'],'slug'=>['nullable','string','max:180','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],'summary'=>['nullable','string','max:2000'],'body_html'=>['nullable','string','max:500000'],'body_delta'=>['nullable','json','max:500000'],'media_id'=>['nullable','exists:media,id'],'duration'=>['nullable','string','max:255'],'fees'=>['nullable','string','max:255'],'offer_verified'=>['sometimes','boolean'],'cta_label'=>['nullable','string','max:80'],'display_order'=>['required','integer','min:0','max:10000'],'featured'=>['sometimes','boolean'],'status'=>['required','in:draft,published'],'sections'=>['nullable','array','max:40'],'sections.*.kind'=>['required','in:audience,concerns,expect,modes'],'sections.*.heading'=>['nullable','string','max:255'],'sections.*.content'=>['required','string','max:1500'],'seo_title'=>['nullable','string','max:255'],'seo_description'=>['nullable','string','max:1000'],'canonical_url'=>['nullable','url:https','max:1000'],'noindex'=>['sometimes','boolean'],'og_title'=>['nullable','string','max:255'],'og_description'=>['nullable','string','max:1000'],'og_image'=>['nullable','url:https','max:1000'],'schema_mode'=>['required','in:automatic,manual'],'manual_schema'=>['nullable','string','max:40000'],'version'=>['nullable','integer','min:1']];}
}
