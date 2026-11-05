<?php
// Isolated CI only. Never package this file for hosting or point it at production.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(getenv('GITHUB_ACTIONS')!=='true'||getenv('DB_HOST')!=='127.0.0.1')throw new RuntimeException('Isolated CI only.');
use Illuminate\Support\Facades\{Artisan,DB,Hash};
foreach(['0001_01_01_000000_create_users_table.php','0001_01_01_000001_create_cache_table.php','0001_01_01_000002_create_jobs_table.php','2026_11_04_000001_create_admin_setup_table.php'] as $path) Artisan::call('migrate',['--force'=>true,'--path'=>'database/migrations/'.$path]);
$hash=Hash::make('Isolated-Previous-Owner123!');
DB::table('users')->insert(['id'=>41,'name'=>'Previous owner fixture','email'=>'prior-owner@example.test','password'=>$hash,'is_admin'=>1,'created_at'=>now(),'updated_at'=>now()]);
DB::table('admin_setup')->update(['token_hash'=>hash('sha256','expired-bootstrap'),'expires_at'=>now()->addDay(),'completed_at'=>now()]);
$before=DB::table('users')->where('id',41)->first();
$sql=[];
DB::listen(function($query)use(&$sql){if(preg_match('/^\s*(create|alter|insert|update|delete)\b/i',$query->sql))$sql[]=$query->toRawSql().';';});
Artisan::call('migrate',['--force'=>true]);
file_put_contents(__DIR__.'/../build/upgrade-20261105.sql',"-- Additive upgrade from four baseline migrations. Back up first. DDL may auto-commit.\n".implode("\n",$sql)."\n");
$after=DB::table('users')->where('id',41)->first();
if($after->password!==$before->password||$after->email!==$before->email||$after->role!=='administrator'||!$after->active||DB::table('admin_setup')->value('token_hash')!==null)throw new RuntimeException('Previous release preservation failed.');
file_put_contents(__DIR__.'/../build/evidence/upgrade-preservation.json',json_encode(['previousOwnerId'=>41,'passwordHashPreserved'=>true,'ownerRole'=>'administrator','bootstrapInvalidated'=>true,'migrations'=>DB::table('migrations')->count()],JSON_PRETTY_PRINT));
