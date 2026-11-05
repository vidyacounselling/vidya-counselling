<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,AdminController,UserController,PostController,TaxonomyController,MediaController,CommentController,ScriptController,BlogController};
Route::view('/','landing')->withoutMiddleware([\Illuminate\Session\Middleware\StartSession::class,\Illuminate\View\Middleware\ShareErrorsFromSession::class,\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class])->name('home');
Route::any('/register',fn()=>abort(404))->name('register');
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'show'])->name('login');Route::post('/login',[AuthController::class,'login'])->middleware('throttle:login');});
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth','staff'])->prefix('admin')->group(function(){
 Route::get('/',AdminController::class)->name('admin');
 Route::middleware('admin')->group(function(){Route::post('/users/{user}/restore',[UserController::class,'restore'])->name('users.restore');Route::resource('users',UserController::class);Route::post('/blogs/{blog}/scripts',[ScriptController::class,'save'])->name('scripts.save');Route::post('/scripts/disable',[ScriptController::class,'disable'])->name('scripts.disable');});
 Route::post('/blogs/bulk',[PostController::class,'bulk'])->name('blogs.bulk');
 Route::post('/blogs/{blog}/restore',[PostController::class,'restore'])->name('blogs.restore');
 Route::post('/blogs/{blog}/autosave',[PostController::class,'autosave'])->middleware('throttle:60,1')->name('blogs.autosave');
 Route::post('/blogs/{blog}/revision/{revision}',[PostController::class,'revision'])->name('blogs.revision');
 Route::get('/blogs/{blog}/preview',[BlogController::class,'preview'])->name('blogs.preview');Route::resource('blogs',PostController::class);
 Route::get('/taxonomy/{kind}',[TaxonomyController::class,'index'])->where('kind','categories|tags')->name('taxonomy.index');
 Route::post('/taxonomy/{kind}/{id?}',[TaxonomyController::class,'save'])->where('kind','categories|tags')->name('taxonomy.save');
 Route::delete('/taxonomy/{kind}/{id}',[TaxonomyController::class,'destroy'])->where('kind','categories|tags')->name('taxonomy.destroy');
 Route::post('/media',[MediaController::class,'store'])->middleware('throttle:uploads')->name('media.store');
 Route::post('/content-preview',function(\Illuminate\Http\Request $r){$d=$r->validate(['body_html'=>['nullable','string','max:500000']]);return response()->json(['html'=>app(\App\Services\ArticleContent::class)->sanitize($d['body_html']??'')]);})->middleware('throttle:60,1');
 Route::get('/comments',[CommentController::class,'index'])->name('comments.index');Route::post('/comments/moderate',[CommentController::class,'moderate'])->name('comments.moderate');
});
Route::get('/media/{filename}',[MediaController::class,'show'])->name('media.show');
Route::get('/blog',[BlogController::class,'index'])->name('blog.index');
Route::get('/blog/archive/{kind}/{slug}',[BlogController::class,'index'])->where('kind','category|tag')->name('blog.archive');
Route::get('/blog/{slug}',[BlogController::class,'show'])->name('blog.show');
Route::post('/blog/{slug}/comments',[CommentController::class,'store'])->middleware('throttle:comments')->name('blog.comment');
Route::get('/sitemap.xml',[BlogController::class,'sitemap'])->name('sitemap');
