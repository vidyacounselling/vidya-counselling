<?php
namespace App\Http\Controllers;
use App\Models\{User,Post,Comment,Activity};
class AdminController extends Controller {
 public function __invoke() { return view('admin.dashboard',['counts'=>['Active users'=>User::where('active',true)->count(),'Published'=>Post::eligible()->count(),'Drafts'=>Post::where('status','draft')->count(),'Scheduled'=>Post::where('status','scheduled')->where('published_at','>',now())->count(),'Pending comments'=>Comment::where('status','pending')->count(),'Public services'=>\App\Models\Service::eligible()->count(),...(auth()->user()->isAdministrator()?['Pending reviews'=>\App\Models\Review::where('status','pending')->count()]:[]),...(auth()->user()->canManageInquiries()?['New inquiries'=>\App\Models\Inquiry::where('status','new')->count()]:[])],'posts'=>Post::with(['author','category'])->latest('updated_at')->limit(6)->get(),'activities'=>Activity::latest()->limit(8)->get(),'pending'=>Comment::where('status','pending')->count()]); }
}
