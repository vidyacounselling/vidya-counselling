<?php
namespace App\Http\Controllers;
use App\Models\{Post,Category,Tag,User,PostRevision,Activity};
use App\Http\Requests\SavePostRequest;
use App\Services\{Editorial,ArticleSchema};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Gate};

class PostController extends Controller {
    public function index(Request $r) {
        $q=Post::with(['author','category','media'])->withCount(['comments','comments as pending_comments_count'=>fn($q)=>$q->where('status','pending')]);
        if($r->status==='trash') $q->onlyTrashed();
        elseif($r->status==='published') $q->eligible();
        elseif($r->status==='scheduled') $q->where('status','scheduled')->where('published_at','>',now());
        elseif($r->status==='draft') $q->where('status','draft');
        if($r->filled('q')) { $s='%'.mb_substr($r->q,0,100).'%'; $q->where(fn($q)=>$q->where('title','like',$s)->orWhere('slug','like',$s)->orWhere('excerpt','like',$s)); }
        foreach(['category_id','author_id','schema_mode'] as $f) if($r->filled($f)) $q->where($f,$r->$f);
        if($r->filled('tag_id')) $q->whereHas('tags',fn($q)=>$q->where('tags.id',$r->tag_id));
        if(in_array($r->comments,['0','1'],true)) $q->where('comments_enabled',$r->comments);
        $date=$r->date_field==='published_at'?'published_at':'created_at';
        \App\Services\DateFilters::apply($q,$r,$date);
        $sort=in_array($r->sort,['title','published_at','created_at','updated_at'],true)?$r->sort:'updated_at';
        return view('posts.index',$this->options()+['posts'=>$q->orderBy($sort,$r->direction==='asc'?'asc':'desc')->paginate(15)->withQueryString()]);
    }
    private function options(?Post $post=null): array { return ['categories'=>Category::orderBy('name')->get(),'tags'=>Tag::orderBy('name')->get(),'authors'=>User::withTrashed()->where(fn($q)=>$q->where(fn($q)=>$q->where('active',true)->whereNull('deleted_at'))->when($post,fn($q)=>$q->orWhere('id',$post->author_id)))->orderBy('name')->get(['id','name'])]; }
    public function create() { return view('posts.form',$this->options()+['post'=>new Post(['author_id'=>auth()->id(),'status'=>'draft','language'=>'en','schema_mode'=>'automatic']),'working'=>null]); }
    public function store(SavePostRequest $r,Editorial $editorial) { $p=$editorial->save(null,$r->validated(),$r->user()->id); return redirect()->route('blogs.edit',$p)->with('success','Post saved.'); }
    public function edit(Post $blog) { Gate::authorize('update',$blog); return view('posts.form',$this->options($blog)+['post'=>$blog->load(['tags','media','scripts']),'working'=>DB::table('post_working_copies')->where('post_id',$blog->id)->where('user_id',auth()->id())->first()]); }
    public function update(SavePostRequest $r,Post $blog,Editorial $editorial) { Gate::authorize('update',$blog); $editorial->save($blog,$r->validated(),$r->user()->id); return redirect()->route('blogs.edit',$blog)->with('success','Changes saved.'); }
    public function show(Post $blog,ArticleSchema $schema) { Gate::authorize('view',$blog); try{$effective=$schema->effective($blog);}catch(\Illuminate\Validation\ValidationException $e){$effective=['source'=>'Invalid manual draft','data'=>$e->errors()];} return view('posts.show',['post'=>$blog->load(['author','category','tags','media','scripts']),'schema'=>$effective,'revisions'=>$blog->revisions()->latest()->limit(20)->get(),'pending'=>$blog->comments()->where('status','pending')->count()]); }
    public function destroy(Post $blog) { Gate::authorize('delete',$blog); $blog->delete(); Activity::record('Post moved to trash','Post #'.$blog->id); return redirect()->route('blogs.index')->with('success','Post moved to trash.'); }
    public function restore(int $blog) { $p=Post::onlyTrashed()->findOrFail($blog); Gate::authorize('restore',$p); $p->status='draft'; $p->restore(); $p->save(); Activity::record('Post restored as draft','Post #'.$p->id); return back()->with('success','Post restored as a draft.'); }
    public function autosave(SavePostRequest $r,Post $blog) {
        Gate::authorize('update',$blog); $data=$r->validated();
        abort_if((int)$data['version']!==$blog->version,409,'Another editor saved this post. Your changes remain in the editor.');
        $data['body_html']=app(\App\Services\ArticleContent::class)->sanitize($data['body_html']??'');
        DB::table('post_working_copies')->updateOrInsert(['post_id'=>$blog->id,'user_id'=>$r->user()->id],['base_version'=>$blog->version,'snapshot'=>json_encode($data,JSON_THROW_ON_ERROR),'created_at'=>now(),'updated_at'=>now()]);
        return response()->json(['saved_at'=>now()->toIso8601String(),'message'=>'Private working copy saved; live article unchanged.']);
    }
    public function revision(Request $r,Post $blog,int $revision,Editorial $editorial) {
        Gate::authorize('update',$blog); $rev=PostRevision::where('post_id',$blog->id)->findOrFail($revision); $a=$rev->snapshot['attributes'];
        $allowed=['title','slug','excerpt','body_html','body_delta','category_id','media_id','author_id','comments_enabled','seo_title','seo_description','canonical_url','noindex','nofollow','og_title','og_description','og_image','focus_phrase','language','schema_mode','manual_schema','sources','reviewer_name','reviewer_credentials'];
        $data=array_intersect_key($a,array_flip($allowed)); $data['status']='draft'; $data['version']=$r->integer('version'); $data['tags']=$rev->snapshot['tags'];
        $editorial->save($blog,$data,$r->user()->id); return redirect()->route('blogs.edit',$blog)->with('success','Revision restored as draft. Review before publishing.');
    }
    public function bulk(Request $r) {
        $data=$r->validate(['ids'=>['required','array','min:1','max:15'],'ids.*'=>['integer','distinct'],'action'=>['required','in:trash,restore,publish,unpublish,category'],'category_id'=>['nullable','exists:categories,id']]);
        DB::transaction(function()use($r,$data){ foreach($data['ids'] as $id) { $p=Post::withTrashed()->whereKey($id)->lockForUpdate()->firstOrFail(); Gate::authorize('update',$p);
            switch($data['action']) { case 'trash':$p->delete();break; case 'restore':$p->status='draft';$p->restore();$p->save();break;
            case 'publish':abort_if($p->trashed(),422);$p->status='published';$p->published_at=$p->published_at??now();app(Editorial::class)->publishable($p);$p->increment('version');$p->save();break;
            case 'unpublish':$p->status='draft';$p->increment('version');$p->save();break;
            case 'category':abort_unless(!empty($data['category_id']),422);$p->category_id=$data['category_id'];$p->increment('version');$p->save();break; }
            Activity::record('Bulk post '.$data['action'],'Post #'.$p->id);
        }}); return back()->with('success','Selected posts updated.');
    }
}
