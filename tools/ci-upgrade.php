<?php
// Isolated CI only. Never package or run against production.
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(getenv('GITHUB_ACTIONS')!=='true'||getenv('DB_HOST')!=='127.0.0.1'||!in_array(getenv('DB_DATABASE'),['vidya_upgrade','vidya_import'],true))throw new RuntimeException('Isolated CI only.');
use Illuminate\Support\Facades\{Artisan,DB,Hash};
$baseline=['0001_01_01_000000_create_users_table.php','0001_01_01_000001_create_cache_table.php','0001_01_01_000002_create_jobs_table.php','2026_11_04_000001_create_admin_setup_table.php','2026_11_05_000001_add_staff_and_editorial.php'];
Artisan::call('migrate',['--force'=>true,'--path'=>array_map(fn($p)=>'database/migrations/'.$p,$baseline)]);
DB::table('migrations')->where('migration','2026_11_05_000001_add_staff_and_editorial')->update(['batch'=>2]);
DB::table('users')->insert(['id'=>41,'name'=>'Previous owner fixture','email'=>'prior-owner@example.test','password'=>Hash::make('Isolated-Previous-Owner123!'),'is_admin'=>1,'role'=>'administrator','active'=>1,'created_at'=>now(),'updated_at'=>now()]);
DB::table('posts')->insert(['id'=>73,'author_id'=>41,'title'=>'Previous release article','slug'=>'previous-release-article','body_html'=>'<p>Preserved previous release article.</p>','status'=>'published','published_at'=>now()->subDay(),'created_at'=>now(),'updated_at'=>now()]);
DB::table('comments')->insert(['id'=>53,'post_id'=>73,'name'=>'Previous reader','email'=>'previous@example.test','body'=>'Preserved previous release comment.','status'=>'approved','created_at'=>now(),'updated_at'=>now()]);
if(getenv('DB_DATABASE')==='vidya_import')exit;
$before=DB::table('users')->where('id',41)->first();$sql=[];
DB::listen(function($query)use(&$sql){if(preg_match('/^\s*(create|alter|insert|update|delete)\b/i',$query->sql))$sql[]=$query->toRawSql().';';});
Artisan::call('migrate',['--force'=>true]);
file_put_contents(__DIR__.'/../build/upgrade-20261107.sql',"-- Additive upgrade from R2 five-migration baseline. Back up first. DDL may auto-commit.\n".implode("\n",$sql)."\n");
$after=DB::table('users')->where('id',41)->first();
if($after->password!==$before->password||$after->email!==$before->email||$after->role!=='administrator'||!$after->active||DB::table('posts')->where('id',73)->value('body_html')!=='<p>Preserved previous release article.</p>'||DB::table('comments')->where('id',53)->value('body')!=='Preserved previous release comment.'||!DB::table('public_pages')->where('reference','post:73')->exists())throw new RuntimeException('Previous release preservation failed.');
file_put_contents(__DIR__.'/../build/evidence/upgrade-preservation.json',json_encode(['previousOwnerId'=>41,'passwordHashPreserved'=>true,'postAndCommentPreserved'=>true,'registryBackfill'=>true,'migrations'=>DB::table('migrations')->count()],JSON_PRETTY_PRINT));
