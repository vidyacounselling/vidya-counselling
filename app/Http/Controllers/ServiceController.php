<?php
namespace App\Http\Controllers;
use App\Models\{Service,Media,Activity};
use App\Services\{ServiceEditor,CentreSchema};
use App\Http\Requests\SaveServiceRequest;
use Illuminate\Http\Request;
class ServiceController extends Controller {
 public function index(Request $r){$q=Service::with('media');if($r->status==='archived')$q->onlyTrashed();elseif(in_array($r->status,['draft','published'],true))$q->where('status',$r->status);if($r->filled('q'))$q->where('name','like','%'.mb_substr($r->q,0,100).'%');return view('centre.index',['kind'=>'services','title'=>'Services','items'=>$q->orderBy('display_order')->orderBy('id')->paginate(15)->withQueryString()]);}
 public function create(){return view('centre.service-form',['service'=>new Service(['display_order'=>0]),'media'=>Media::latest()->limit(100)->get()]);}
 public function store(SaveServiceRequest $r,ServiceEditor $editor){$s=$editor->save(null,$r->validated(),$r->user()->id);return redirect()->route('services-admin.edit',$s)->with('success','Service saved.');}
 public function show(Service $service){try{$schema=app(CentreSchema::class)->service($service->load('sections'));}catch(\Illuminate\Validation\ValidationException $e){$schema=['source'=>'Invalid manual draft','data'=>$e->errors()];}return view('centre.service-show',compact('service','schema'));}
 public function edit(Service $service){return view('centre.service-form',['service'=>$service->load(['sections','media']),'media'=>Media::latest()->limit(100)->get()]);}
 public function update(SaveServiceRequest $r,Service $service,ServiceEditor $editor){$editor->save($service,$r->validated(),$r->user()->id);return redirect()->route('services-admin.edit',$service)->with('success','Service updated.');}
 public function destroy(Service $service){$service->delete();Activity::record('Service archived','Service #'.$service->id);return redirect()->route('services-admin.index')->with('success','Service archived; historical requests remain intact.');}
 public function restore(int $service){$s=Service::onlyTrashed()->findOrFail($service);$s->status='draft';$s->restore();$s->save();Activity::record('Service restored as draft','Service #'.$s->id);return back()->with('success','Service restored as draft.');}
 public function preview(Service $service){$service->load(['media','sections']);return response()->view('public.service',['service'=>$service,'schema'=>app(CentreSchema::class)->service($service),'services'=>Service::eligible()->with('media')->get(),'formToken'=>app(\App\Services\PublicSubmissions::class)->token(request())])->header('X-Robots-Tag','noindex, nofollow, noarchive')->header('Cache-Control','private, no-store');}
 public function reorder(Request $r){$d=$r->validate(['orders'=>['required','array','max:100'],'orders.*'=>['integer','min:0','max:10000']]);\Illuminate\Support\Facades\DB::transaction(function()use($d){foreach($d['orders'] as $id=>$order)Service::findOrFail((int)$id)->update(['display_order'=>$order]);});Activity::record('Service display order updated','Services');return back()->with('success','Display order saved.');}
}
