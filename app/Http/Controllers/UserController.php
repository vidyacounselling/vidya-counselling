<?php
namespace App\Http\Controllers;
use App\Models\{User,Activity};
use App\Http\Requests\SaveUserRequest;
use App\Services\StaffAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller {
    public function index(Request $r) {
        $q=User::query()->withCount('posts');
        if($r->status==='trash') $q->onlyTrashed();
        elseif(in_array($r->status,['active','inactive'],true)) $q->where('active',$r->status==='active');
        if($r->filled('q')) $q->where(fn($s)=>$s->where('name','like','%'.mb_substr($r->q,0,100).'%')->orWhere('email','like','%'.mb_substr($r->q,0,100).'%'));
        if(in_array($r->role,['administrator','editor'],true)) $q->where('role',$r->role);
        foreach(['from'=>'>=','to'=>'<='] as $key=>$op) if($r->filled($key)&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$r->$key)) $q->whereDate('created_at',$op,$r->$key);
        $sort=in_array($r->sort,['name','email','role','created_at'],true)?$r->sort:'created_at';
        return view('users.index',['users'=>$q->orderBy($sort,$r->direction==='asc'?'asc':'desc')->paginate(15)->withQueryString()]);
    }
    public function create() { return view('users.form',['user'=>new User(['active'=>true,'role'=>'editor'])]); }
    public function store(SaveUserRequest $r,StaffAccounts $service) { $u=$service->save($r->user(),null,$r->validated()); return redirect()->route('users.show',$u)->with('success','Staff account created. Share credentials through your approved private channel.'); }
    public function show(User $user) { return view('users.show',['user'=>$user->loadCount('posts'),'posts'=>$user->posts()->latest()->limit(5)->get()]); }
    public function edit(User $user) { return view('users.form',compact('user')); }
    public function update(SaveUserRequest $r,User $user,StaffAccounts $service) { $service->save($r->user(),$user,$r->validated()); return redirect()->route('users.show',$user)->with('success','Staff account updated.'); }
    public function destroy(Request $r,User $user,StaffAccounts $service) { $service->save($r->user(),$user,[],true); return redirect()->route('users.index')->with('success','Account moved to trash. Authored posts and attribution are preserved.'); }
    public function restore(int $user) { DB::transaction(function()use($user){ DB::table('staff_locks')->where('id',1)->lockForUpdate()->first(); $u=User::onlyTrashed()->findOrFail($user); $u->active=false; $u->save(); $u->restore(); Activity::record('Staff restored inactive','User #'.$u->id); }); return back()->with('success','Account restored as inactive. Review before activating.'); }
}
