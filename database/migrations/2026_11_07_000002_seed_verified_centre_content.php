<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  $settings=json_decode(file_get_contents(__DIR__.'/../content/verified-centre.json'),true,512,JSON_THROW_ON_ERROR);
  foreach($settings as $key=>$value)DB::table('site_settings')->insertOrIgnore(['key'=>$key,'value'=>$value,'created_at'=>now(),'updated_at'=>now()]);
  $services=[
   ['Career & Education Counseling','career-education-counselling','Make space to understand your interests, explore your choices and think about what comes next.',['Students','Parents','Working professionals'],['Academic pressure and student concerns','Stream, course and career choices','Career exploration and transitions']],
   ['Mental Health Counseling','mental-health-counselling','A supportive conversation about feelings and experiences that may be difficult to carry alone.',['Adolescents','Adults','Working professionals'],['Stress and emotional concerns','Self-esteem and life transitions','Grief, loneliness and feeling overwhelmed']],
   ['Marriage & Family Counseling','marriage-family-counselling','Explore patterns, communication and the challenges that affect relationships and family life.',['Couples','Parents','Families'],['Relationship and communication concerns','Parenting challenges','Family dynamics and understanding']],
  ];
  foreach($services as $i=>$data){$id=DB::table('services')->insertGetId(['name'=>$data[0],'slug'=>$data[1],'summary'=>$data[2],'body_html'=>'<p>'.$data[2].'</p><h2 id="section-1">A thoughtful first conversation</h2><p>Begin by sharing what feels comfortable. Discuss your concerns and appropriate next steps at a pace that works for you.</p>','status'=>'published','featured'=>true,'display_order'=>$i+1,'created_at'=>now(),'updated_at'=>now()]);
   foreach(['audience'=>$data[3],'concerns'=>$data[4],'expect'=>['Share what feels comfortable','Discuss your concerns, preferences and next steps'],'modes'=>['In-person','Online']] as $kind=>$items)foreach($items as $order=>$content)DB::table('service_sections')->insert(['service_id'=>$id,'kind'=>$kind,'content'=>$content,'display_order'=>$order]);
   DB::table('public_pages')->insert(['reference'=>'service:'.$id,'kind'=>'service','entity_id'=>$id,'title'=>$data[0],'path'=>'/services/'.$data[1],'status'=>'published','created_at'=>now(),'updated_at'=>now()]);
  }
 }
 public function down():void{throw new RuntimeException('Preserve edited production content; use a reviewed forward change.');}
};
