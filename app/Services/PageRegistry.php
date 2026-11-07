<?php
namespace App\Services;
use App\Models\{PublicPage,Service,Post,Category,Tag,Faq};
class PageRegistry {
 public function sync($model):void {
  $kind=match(true){$model instanceof Service=>'service',$model instanceof Post=>'post',$model instanceof Category=>'category',$model instanceof Tag=>'tag',default=>null};if(!$kind)return;
  $path=match($kind){'service'=>'/services/'.$model->slug,'post'=>'/blog/'.$model->slug,default=>'/blog/archive/'.$kind.'/'.$model->slug};
  $status=!$model->exists?'archived':(method_exists($model,'trashed')&&$model->trashed()?'archived':($model->status??'published'));
  PublicPage::updateOrCreate(['reference'=>$kind.':'.$model->id],['kind'=>$kind,'entity_id'=>$model->id,'title'=>$model->title??$model->name,'path'=>$path,'status'=>$status]);
 }
 public function current():?PublicPage{return PublicPage::where('path','/'.trim(request()->path(),'/'))->first()??(request()->path()==='/'?PublicPage::where('reference','core:home')->first():null);}
 public function faqs(?PublicPage $page){return $page&&$page->eligible()?$page->faqs()->where('status','published')->get():collect();}
 public function suggestions(PublicPage $page):array {
  $items=[['question'=>'How can I request a consultation?','answer'=>'Use the Request a Consultation form. A request is an inquiry, not a confirmed appointment.']];
  if($page->kind==='service'&&($service=Service::with('sections')->find($page->entity_id))){$items[]=['question'=>'Who is '.$service->name.' for?','answer'=>$service->facts('audience')->pluck('content')->join(', ').'.'];$modes=$service->facts('modes')->pluck('content')->join(' and ');if($modes)$items[]=['question'=>'Which consultation modes are available for '.$service->name.'?','answer'=>'The listed modes are '.$modes.'. Contact the centre to discuss suitability and availability.'];}
  $items[]=['question'=>'What are the fees and session duration?','answer'=>''];
  return array_values(array_filter($items,function($item){$key=$this->questionKey($item['question']);if(Faq::withTrashed()->where('question_key',$key)->exists())return false;$existing=Faq::limit(500)->pluck('question');foreach($existing as $question)if(levenshtein(mb_strtolower($question),mb_strtolower($item['question']))<8)return false;return true;}));
 }
 public function questionKey(string $q):string{return hash('sha256',preg_replace('/[^\pL\pN]+/u',' ',mb_strtolower(trim($q))));}
}
