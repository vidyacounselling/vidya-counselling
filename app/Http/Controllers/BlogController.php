<?php
namespace App\Http\Controllers;
use App\Models\{Post,Category,Tag};
use App\Services\{ArticleContent,ArticleSchema};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Gate};
class BlogController extends Controller {
    public function index(Request $r,?string $kind=null,?string $slug=null) {
        $q=Post::eligible()->with(['author','category','media']); $taxonomy=null;
        if($kind) { $model=$kind==='category'?Category::class:Tag::class; $taxonomy=$model::where('slug',$slug)->firstOrFail(); if($kind==='category') $q->where('category_id',$taxonomy->id); else $q->whereHas('tags',fn($q)=>$q->where('tags.id',$taxonomy->id)); }
        if($r->filled('q')) { $s='%'.mb_substr($r->q,0,100).'%';$q->where(fn($q)=>$q->where('title','like',$s)->orWhere('excerpt','like',$s)); }
        $posts=$q->orderByDesc('published_at')->paginate(9)->withQueryString();
        return response()->view('blog.index',compact('posts','taxonomy','kind'))->header('Cache-Control','no-cache');
    }
    public function show(string $slug) {
        $post=Post::eligible()->with(['author','category','tags','media'])->where('slug',$slug)->first();
        if(!$post) { $old=DB::table('post_slugs')->where('slug',$slug)->first(); if($old && ($p=Post::eligible()->find($old->post_id))) return redirect($p->public_url,301); abort(404); }
        return $this->article($post,false);
    }
    public function preview(Post $blog) { Gate::authorize('view',$blog); return $this->article($blog->load(['author','category','tags','media']),true); }
    private function article(Post $post,bool $preview) {
        $related=Post::eligible()->where('id','!=',$post->id)->where(fn($q)=>$q->where('category_id',$post->category_id)->orWhereHas('tags',fn($q)=>$q->whereIn('tags.id',$post->tags->pluck('id'))))->with(['media','author'])->limit(3)->get();
        $comments=$post->comments()->where('status','approved')->latest()->paginate(20,['id','post_id','name','body','created_at']);
        $toc=app(ArticleContent::class)->toc($post->body_html??'');
        $schema=$preview?null:app(ArticleSchema::class)->encoded($post);
        $response=response()->view('blog.show',compact('post','preview','related','comments','toc','schema'))->header('Cache-Control','private, no-store')->header('Content-Security-Policy',"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' https: data:; object-src 'none'; frame-src 'none'; base-uri 'self'; form-action 'self'");
        if($preview) $response->header('X-Robots-Tag','noindex, nofollow, noarchive'); return $response;
    }
    public function sitemap() { $posts=Post::eligible()->where('noindex',false)->get()->filter(fn($p)=>$p->canonical===$p->public_url); return response()->view('blog.sitemap',compact('posts'))->header('Content-Type','application/xml; charset=UTF-8')->header('Cache-Control','no-cache'); }
}
