<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class SavePostRequest extends FormRequest {
    public function authorize(): bool { return $this->user()?->isStaff()??false; }
    public function rules(): array { return [
        'title'=>['nullable','string','max:255'],'slug'=>['nullable','string','max:180','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
        'excerpt'=>['nullable','string','max:1500'],'body_html'=>['nullable','string','max:500000'],'body_delta'=>['nullable','json','max:500000'],
        'category_id'=>['nullable','exists:categories,id'],'tags'=>['nullable','array','max:30'],'tags.*'=>['integer','exists:tags,id'],
        'author_id'=>['required',Rule::exists('users','id')->whereNull('deleted_at')],'media_id'=>['nullable','exists:media,id'],
        'status'=>['required',Rule::in(['draft','published','scheduled'])],'published_at'=>['nullable','date_format:Y-m-d\TH:i'],
        'comments_enabled'=>['sometimes','boolean'],'seo_title'=>['nullable','string','max:255'],'seo_description'=>['nullable','string','max:1000'],
        'canonical_url'=>['nullable','url:https','max:1000'],'noindex'=>['sometimes','boolean'],'nofollow'=>['sometimes','boolean'],
        'og_title'=>['nullable','string','max:255'],'og_description'=>['nullable','string','max:1000'],'og_image'=>['nullable','url:https','max:1000'],
        'focus_phrase'=>['nullable','string','max:255'],'language'=>['required',Rule::in(['en','mr'])],
        'schema_mode'=>['required',Rule::in(['automatic','manual'])],'manual_schema'=>['nullable','string','max:40000'],
        'sources'=>['nullable','string','max:12000'],'reviewer_name'=>['nullable','string','max:255'],'reviewer_credentials'=>['nullable','string','max:255'],
        'reviewed_at'=>['nullable','date_format:Y-m-d\TH:i'],'version'=>['nullable','integer','min:1'],
        'script_name'=>['prohibited'],'script_code'=>['prohibited'],'scripts'=>['prohibited']]; }
}
