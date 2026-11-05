<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(getenv('GITHUB_ACTIONS')!=='true'||getenv('DB_DATABASE')!=='vidya_race')throw new RuntimeException('Disposable CI database only.');
use App\Models\User;
use Illuminate\Support\Facades\DB;
$ids=[];for($i=0;$i<2;$i++){$u=User::create(['name'=>'Race fixture','email'=>'race'.$i.'@example.test','password'=>'Disposable-Only123!']);$u->forceFill(['role'=>'administrator','active'=>true,'is_admin'=>true])->save();$ids[]=$u->id;}
DB::disconnect();$children=[];$dir=sys_get_temp_dir().'/vidya-race-'.bin2hex(random_bytes(6));mkdir($dir,0700);
foreach($ids as $id){$pid=pcntl_fork();if($pid===0){DB::purge();$u=User::findOrFail($id);try{app(\App\Services\StaffAccounts::class)->save($u,$u,['role'=>'editor','active'=>true]);$out='demoted';}catch(\Illuminate\Validation\ValidationException $e){$out='protected';}file_put_contents($dir.'/'.$id,$out);exit(0);} $children[]=$pid;}
foreach($children as $pid){pcntl_waitpid($pid,$status);if(pcntl_wexitstatus($status)!==0)throw new RuntimeException('Concurrent worker failed.');}
DB::purge();$results=array_map(fn($id)=>file_get_contents($dir.'/'.$id),$ids);sort($results);
if($results!==['demoted','protected']||User::where('role','administrator')->where('active',true)->count()!==1)throw new RuntimeException('Last-active-admin invariant failed.');
echo json_encode(['lastActiveAdministratorRace'=>'pass','activeAdministrators'=>1,'outcomes'=>$results],JSON_PRETTY_PRINT);
