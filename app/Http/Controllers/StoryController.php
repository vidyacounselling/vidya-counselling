<?php
namespace App\Http\Controllers;
use App\Models\{ClientStory,Service,Media,Activity};
use App\Http\Requests\SaveStoryRequest;
use App\Services\ArticleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
class StoryController extends Controller {
 public function index(Request $r){$q=ClientStory::with('service');if($r->status==='trash')$q->onlyTrashed();elseif(in_array($r->status,['draft','published','archived'],true))$q->where('status',$r->status);if($r->filled('q'))$q->where('title','like','%'.mb_substr($r->q,0,100).'%');if($r->filled('consent'))$q->where('consent_status',$r->consent);return view('centre.index',['kind'=>'stories','title'=>'Client Stories','items'=>$q->orderBy('display_order')->latest()->paginate(15)->withQueryString()]);}
 private function options():array{return ['services'=>Service::orderBy('name')->get(),'media'=>Media::latest()->limit(100)->get()];}
 public function create(){return view('centre.story-form',$this->options()+['story'=>new ClientStory(['anonymous'=>true,'status'=>'draft','consent_status'=>'pending','source_type'=>'written','display_order'=>0])]);}
 public function edit(ClientStory $story){return view('centre.story-form',$this->options()+compact('story'));}
 public function show(ClientStory $story){return view('centre.story-show',compact('story'));}
 public function store(SaveStoryRequest $r){return $this->save($r,new ClientStory);}
 public function update(SaveStoryRequest $r,ClientStory $story){return $this->save($r,$story);}
 private function save(SaveStoryRequest $r,ClientStory $story){$d=$r->validated();foreach(['identity_consent','media_consent','source_consent','featured'] as $key)$d[$key]=(bool)($d[$key]??false);$d['story_html']=app(ArticleContent::class)->sanitize($d['story_html']??'');$d['consented_at']=empty($d['consented_at'])?null:Carbon::createFromFormat('Y-m-d\TH:i',$d['consented_at'],'Asia/Kolkata')->utc();if($d['status']==='published'){$errors=[];if($d['consent_status']!=='approved'||!$d['consented_at'])$errors['consent_status']='Record approved publication consent and its actual date before publishing.';if(!$d['anonymous']&&(!$d['identity_consent']||!trim($d['display_name']??'')))$errors['display_name']='Identified publication requires a name and explicit identity consent.';if(!empty($d['media_id'])&&!$d['media_consent'])$errors['media_consent']='Record permission to publish this specific media, or remove it.';if($errors)throw ValidationException::withMessages($errors);}$d['updated_by']=$r->user()->id;if(!$story->exists)$d['original_quotation']=$d['quotation'];\Illuminate\Support\Facades\DB::transaction(function()use($story,$d){\Illuminate\Support\Facades\DB::table('staff_locks')->where('id',1)->lockForUpdate()->first();if($d['status']==='published')app(\App\Services\FeedbackPublication::class)->check($d['quotation'],'story',$story->id);$story->fill($d)->save();});Activity::record('Client story saved','Story #'.$story->id);return redirect()->route('stories.edit',$story)->with('success','Client story saved. Private source/consent notes are never public.');}
 public function destroy(ClientStory $story){$story->delete();Activity::record('Client story moved to trash','Story #'.$story->id);return redirect()->route('stories.index')->with('success','Story moved to trash.');}
 public function restore(int $story){$s=ClientStory::onlyTrashed()->findOrFail($story);$s->status='draft';$s->restore();$s->save();return back()->with('success','Story restored as draft; recheck consent before publishing.');}
}
