<?php
namespace App\Http\Controllers;
use App\Models\{Service,ClientStory,Review,Post};
use App\Services\{WebsiteSettings,PublicSubmissions,CentreSchema};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CentreController extends Controller {
 private function data(Request $r):array{return ['services'=>Service::eligible()->with('media')->orderBy('display_order')->get(),'formToken'=>app(PublicSubmissions::class)->token($r)];}
 public function home(Request $r){return view('public.home',$this->data($r)+['stories'=>ClientStory::eligible()->with('media')->orderByDesc('featured')->orderBy('display_order')->limit(3)->get(),'reviews'=>Review::eligible()->orderByDesc('featured')->orderBy('display_order')->limit(3)->get(),'posts'=>Post::eligible()->with(['media','author'])->latest('published_at')->limit(3)->get()]);}
 public function services(Request $r){return view('public.services',$this->data($r));}
 public function service(Request $r,string $slug){$s=Service::where('slug',$slug)->first();if(!$s){$old=DB::table('service_slugs')->where('slug',$slug)->first();$s=$old?Service::find($old->service_id):null;abort_unless($s?->isPublic(),404);return redirect($s->public_url,301);}abort_unless($s->isPublic(),404);return view('public.service',$this->data($r)+['service'=>$s->load(['media','sections']),'schema'=>app(CentreSchema::class)->service($s)]);}
 public function story(Request $r){$id=app(WebsiteSettings::class)->get('founder_media_id');return view('public.story',$this->data($r)+['portrait'=>$id?\App\Models\Media::find($id):null]);}
 public function reviews(Request $r){return view('public.reviews',$this->data($r)+['reviews'=>Review::eligible()->with('service')->orderByDesc('featured')->orderBy('display_order')->latest()->paginate(12)]);}
 public function stories(Request $r){return view('public.stories',$this->data($r)+['stories'=>ClientStory::eligible()->with(['media','service'])->orderByDesc('featured')->orderBy('display_order')->paginate(12)]);}
 public function contact(Request $r){return view('public.contact',$this->data($r)+['selectedService'=>Service::eligible()->find($r->integer('service'))]);}
 public function privacy(Request $r){return view('public.privacy',$this->data($r));}
}
