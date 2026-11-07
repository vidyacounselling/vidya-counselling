<?php
namespace App\Http\Controllers;
use App\Services\{WebsiteSettings,ArticleContent};
use App\Models\{Media,Activity};
use Illuminate\Http\Request;
class WebsiteController extends Controller {
 public function edit(WebsiteSettings $s){return view('centre.website',['settings'=>$s->all(),'media'=>Media::latest()->limit(100)->get()]);}
 public function update(Request $r,WebsiteSettings $s){$rules=[];foreach(['brand_name','founder_name','hero_title','phone','whatsapp','service_area','opening_hours','consent_version','review_consent_version'] as $k)$rules[$k]=['nullable','string','max:255'];foreach(['founder_intro','hero_description','default_description','address','consent_copy'] as $k)$rules[$k]=['nullable','string','max:2000'];$rules['story_html']=['nullable','string','max:100000'];$rules['email']=['nullable','email','max:254'];foreach(['map_url','instagram','facebook'] as $k)$rules[$k]=['nullable','url:http,https','max:1000'];foreach(['founder_media_id','logo_media_id','default_social_image'] as $k)$rules[$k]=['nullable','exists:media,id'];foreach(['contact_verified','whatsapp_verified','faq_schema_enabled'] as $k)$rules[$k]=['required','boolean'];$rules['inquiry_retention_days']=['required','integer','between:30,3650'];$d=$r->validate($rules);$d['story_html']=app(ArticleContent::class)->sanitize($d['story_html']??'');$d['brand_name']='Vidya Counseling Centre';$s->save($d);Activity::record('Website settings updated','Centre settings');return back()->with('success','Website settings saved. Review retention manually; no automatic deletion is scheduled.');}
}
