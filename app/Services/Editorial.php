<?php
namespace App\Services;
use App\Models\{Post,PostRevision,Activity};
use Illuminate\Support\{Str,Carbon};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Editorial {
    public function save(?Post $post,array $data,int $actor): Post {
        return DB::transaction(function()use($post,$data,$actor){
            // Shared row serializes slug reservation across current and historical namespaces.
            DB::table('staff_locks')->where('id',1)->lockForUpdate()->first();
            $p=$post?Post::whereKey($post->id)->lockForUpdate()->firstOrFail():new Post;
            if($p->exists && (int)($data['version']??0)!==$p->version) throw ValidationException::withMessages(['version'=>'Another editor saved this post. Your draft is retained; reload and compare before saving.']);
            $tags=$data['tags']??[]; unset($data['tags'],$data['version']);
            $slug=$data['slug']??null;
            if(!$slug) $slug=$p->slug ?: (Str::slug($data['title']??'') ?: 'draft-'.Str::lower(Str::random(12)));
            if(Post::withTrashed()->where('slug',$slug)->when($p->exists,fn($q)=>$q->where('id','!=',$p->id))->exists() || DB::table('post_slugs')->where('slug',$slug)->when($p->exists,fn($q)=>$q->where('post_id','!=',$p->id))->exists()) throw ValidationException::withMessages(['slug'=>'This current or historical URL is already reserved.']);
            $data['slug']=$slug;
            foreach(['comments_enabled','noindex','nofollow'] as $key) $data[$key]=(bool)($data[$key]??false);
            foreach(['published_at','reviewed_at'] as $key) $data[$key]=empty($data[$key])?null:Carbon::createFromFormat('Y-m-d\TH:i',$data[$key],'Asia/Kolkata')->utc();
            if($data['status']==='published' && !$data['published_at']) $data['published_at']=$p->published_at??now();
            $data['body_html']=app(ArticleContent::class)->sanitize($data['body_html']??'');
            if(!empty($data['canonical_url']) && (parse_url($data['canonical_url'],PHP_URL_QUERY)||parse_url($data['canonical_url'],PHP_URL_FRAGMENT))) throw ValidationException::withMessages(['canonical_url'=>'Use a canonical HTTPS URL without a query or fragment.']);
            $before=$p->exists?$p->getAttributes():null; $oldSlug=$p->slug; $wasPublic=$p->exists&&$p->isPublic();
            $p->fill($data);
            if($p->status!=='draft') $this->publishable($p);
            if($before) { PostRevision::create(['post_id'=>$p->id,'user_id'=>$actor,'snapshot'=>['attributes'=>$before,'tags'=>$p->tags()->pluck('tags.id')->all()]]); }
            if(!$p->exists || $p->isDirty(['title','body_html','excerpt','media_id','sources','reviewer_name','reviewer_credentials','reviewed_at'])) $p->content_updated_at=now();
            $p->version=($p->version?:0)+1; $p->save(); $p->tags()->sync($tags);
            if($oldSlug && $oldSlug!==$p->slug && $wasPublic) DB::table('post_slugs')->updateOrInsert(['slug'=>$oldSlug],['post_id'=>$p->id,'created_at'=>now(),'updated_at'=>now()]);
            // An old URL pointing directly to the record cannot loop when reclaimed by the same record.
            DB::table('post_slugs')->where('slug',$p->slug)->where('post_id',$p->id)->delete();
            DB::table('post_working_copies')->where('post_id',$p->id)->where('user_id',$actor)->delete();
            $keep=PostRevision::where('post_id',$p->id)->latest('id')->limit(20)->pluck('id'); PostRevision::where('post_id',$p->id)->whereNotIn('id',$keep)->delete();
            Activity::record('Post saved: '.$p->effective_status,'Post #'.$p->id);
            return $p;
        });
    }
    public function publishable(Post $p): void {
        $errors=[];
        if(!trim($p->title)) $errors['title']='Add an article title before publishing.';
        if(mb_strlen(trim(strip_tags($p->body_html??'')))<30) $errors['body_html']='Add at least 30 characters of article text before publishing.';
        if(!$p->category_id) $errors['category_id']='Choose a primary category before publishing.';
        if($p->status==='scheduled' && (!$p->published_at || $p->published_at->lte(now()))) $errors['published_at']='Choose a future time in Asia/Kolkata for scheduled publication.';
        if($p->status==='published' && $p->published_at?->gt(now())) $errors['published_at']='Use Scheduled for a future publication time.';
        if($p->media_id && !trim($p->media?->alt??'')) $errors['media_id']='Add meaningful alternative text to the featured image.';
        if($p->reviewer_name && !$p->reviewed_at) $errors['reviewed_at']='Record the actual review date, or clear the reviewer claim.';
        if($errors) throw ValidationException::withMessages($errors);
        if($p->schema_mode==='manual') app(ArticleSchema::class)->manual($p->manual_schema,$p);
    }
}
