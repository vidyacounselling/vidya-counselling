<?php
namespace App\Http\Controllers;
use App\Models\{Post,Activity,BlogScript};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Gate,DB};
class ScriptController extends Controller {
    public function save(Request $r,Post $blog) {
        Gate::authorize('scripts',$blog);
        $d=$r->validate(['name'=>['required','string','max:120'],'description'=>['nullable','string','max:1000'],'placement'=>['required','in:head,body'],'configuration'=>['required','json','max:4000'],'consent'=>['required','in:analytics,marketing'],'enabled'=>['required','boolean']]);
        // Approved structured configuration only; never arbitrary executable snippets.
        $config=json_decode($d['configuration'],true); abort_unless(is_array($config)&&($config['provider']??'')==='google-analytics'&&preg_match('/^G-[A-Z0-9]{6,20}$/',$config['measurement_id']??''),422,'Use provider google-analytics with a valid G- measurement_id.');
        if($d['enabled']) return back()->withErrors(['scripts'=>'Activation is unavailable: visitor consent has not been implemented. Nonessential tracking remains disabled.']);
        $d['enabled']=false; $d['changed_by']=$r->user()->id; $blog->scripts()->create($d); Activity::record('Disabled script configuration saved','Post #'.$blog->id);
        return back()->with('success','Disabled provider configuration saved. It will not execute.');
    }
    public function disable(Request $r) { abort_unless($r->user()->isAdministrator(),403); DB::table('site_settings')->updateOrInsert(['key'=>'blog_scripts_disabled'],['value'=>'1','updated_at'=>now()]); BlogScript::query()->update(['enabled'=>false]); Activity::record('All blog scripts disabled','Global');return back()->with('success','All blog scripts are disabled globally.'); }
}
