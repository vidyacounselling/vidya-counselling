<?php
namespace App\Http\Controllers;
use App\Models\{Post,Comment,Activity};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CommentController extends Controller {
    public function store(Request $r,string $slug) {
        $p=Post::eligible()->where('slug',$slug)->firstOrFail(); abort_unless($p->comments_enabled,404);
        $d=$r->validate(['name'=>['required','string','max:100'],'email'=>['required','email','max:254'],'body'=>['required','string','min:5','max:3000'],'website'=>['nullable','max:0']]);
        if($d['body']!==strip_tags($d['body'])) return back()->withErrors(['body'=>'Use plain text without HTML.'])->withInput($r->except('email'));
        Comment::create(['post_id'=>$p->id,'name'=>$d['name'],'email'=>mb_strtolower($d['email']),'body'=>$d['body'],'status'=>'pending']);
        return redirect($p->public_url.'#comments')->with('success','Thank you. Your comment is awaiting moderation; it is not public yet.');
    }
    public function index(Request $r) {
        $q=Comment::with('post'); if(in_array($r->status,['pending','approved','spam','trash'],true)) $q->where('status',$r->status);
        if($r->filled('post_id')) $q->where('post_id',$r->post_id);
        if($r->filled('q')) { $s='%'.mb_substr($r->q,0,100).'%';$q->where(fn($q)=>$q->where('body','like',$s)->orWhere('name','like',$s)); }
        foreach(['from'=>'>=','to'=>'<='] as $k=>$op) if($r->filled($k)&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$r->$k)) $q->whereDate('created_at',$op,$r->$k);
        return view('comments.index',['comments'=>$q->latest()->paginate(20)->withQueryString()]);
    }
    public function moderate(Request $r) {
        $d=$r->validate(['ids'=>['required','array','min:1','max:20'],'ids.*'=>['integer','distinct'],'action'=>['required','in:pending,approved,spam,trash,restore,delete'],'confirm'=>['required','accepted']]);
        DB::transaction(function()use($d,$r){ foreach($d['ids'] as $id) { $c=Comment::whereKey($id)->lockForUpdate()->firstOrFail();
            if($d['action']==='delete') { abort_unless($c->status==='trash',422,'Move a comment to trash before permanent deletion.');$c->delete(); }
            else { $status=$d['action']==='restore'?($c->previous_status?:'pending'):$d['action']; $c->previous_status=$c->status==='trash'?$c->previous_status:$c->status;$c->status=$status;$c->moderator_id=$r->user()->id;$c->moderated_at=now();$c->save(); }
            Activity::record('Comment '.$d['action'],'Comment #'.$id);
        }}); return back()->with('success','Selected comments updated.');
    }
}
