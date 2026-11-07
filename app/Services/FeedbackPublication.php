<?php
namespace App\Services;
use App\Models\{Review,ClientStory};
use Illuminate\Validation\ValidationException;
class FeedbackPublication {
 public function check(string $text,string $kind,?int $id=null):void{
  if(preg_match('/[\w.+-]+@[\w.-]+\.[a-z]{2,}|(?:\+?\d[\s().-]*){9,}/iu',$text))throw ValidationException::withMessages([$kind==='review'?'public_text':'quotation'=>'Remove email addresses and phone numbers from public feedback.']);
  $normal=fn($v)=>preg_replace('/\s+/u',' ',mb_strtolower(trim(strip_tags($v))));$key=$normal($text);
  $reviews=Review::where('status','approved')->when($kind==='review'&&$id,fn($q)=>$q->where('id','!=',$id))->get(['public_text','original_text']);
  $stories=ClientStory::eligible()->when($kind==='story'&&$id,fn($q)=>$q->where('id','!=',$id))->get(['quotation']);
  foreach($reviews as $record)if($normal($record->public_text?:$record->original_text)===$key)throw ValidationException::withMessages(['status'=>'This feedback is already published as a review. Reuse the existing record.']);
  foreach($stories as $record)if($normal($record->quotation)===$key)throw ValidationException::withMessages(['status'=>'This feedback is already published as a client story. Reuse the existing record.']);
 }
}
