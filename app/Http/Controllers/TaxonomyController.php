<?php
namespace App\Http\Controllers;
use App\Models\{Category,Tag,Activity};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class TaxonomyController extends Controller {
    private function model(string $kind): string { abort_unless(in_array($kind,['categories','tags'],true),404); return $kind==='categories'?Category::class:Tag::class; }
    public function index(Request $r,string $kind) { $m=$this->model($kind); $q=$m::withCount('posts'); if($r->filled('q')) $q->where('name','like','%'.mb_substr($r->q,0,100).'%'); return view('taxonomy.index',['kind'=>$kind,'items'=>$q->orderBy('name')->paginate(20)->withQueryString(),'editing'=>$r->integer('edit')?$m::findOrFail($r->integer('edit')):new $m]); }
    public function save(Request $r,string $kind,?int $id=null) {
        $m=$this->model($kind); $item=$id?$m::findOrFail($id):new $m;
        $r->merge(['slug'=>$r->slug?:Str::slug($r->name)]);
        $data=$r->validate(['name'=>['required','string','max:120',Rule::unique($kind)->ignore($id)],'slug'=>['required','max:160','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',Rule::unique($kind)->ignore($id)],'description'=>['nullable','string','max:3000'],'seo_title'=>['nullable','string','max:255'],'seo_description'=>['nullable','string','max:1000']]);
        $item->fill($data)->save(); Activity::record('Taxonomy saved',$kind.' #'.$item->id);
        if($r->expectsJson()) return response()->json(['id'=>$item->id,'name'=>$item->name],201);
        return redirect()->route('taxonomy.index',$kind)->with('success','Saved successfully.');
    }
    public function destroy(string $kind,int $id) { $m=$this->model($kind); $item=$m::findOrFail($id); if($item->posts()->withTrashed()->exists()) return back()->withErrors(['taxonomy'=>'This item is used by posts. Reassign/remove it from all posts (including trash) before deleting.']); $item->delete(); Activity::record('Taxonomy deleted',$kind.' #'.$id); return back()->with('success','Unused taxonomy item deleted.'); }
}
