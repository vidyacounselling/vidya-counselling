<?php
namespace App\Http\Controllers;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class MediaController extends Controller {
    public function store(Request $r) {
        $d=$r->validate(['image'=>['required','file','image','mimes:jpg,jpeg,png,webp','max:5120','dimensions:min_width=40,min_height=40,max_width=6000,max_height=6000'],'alt'=>['required','string','max:255'],'caption'=>['nullable','string','max:255'],'credit'=>['nullable','string','max:255']]);
        $file=$r->file('image'); $mime=$file->getMimeType(); $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null; abort_unless($ext,422);
        [$width,$height]=getimagesize($file->getRealPath()); $filename=bin2hex(random_bytes(20)).'.'.$ext;
        // Decode and re-encode to remove embedded metadata/polyglot content; GD is verified in remote and hosting checks.
        abort_unless(function_exists('imagecreatefromstring'),503,'Image processing unavailable.');
        $image=@imagecreatefromstring(file_get_contents($file->getRealPath())); abort_unless($image,422);
        if($width>2000||$height>2000) { $scale=min(2000/$width,2000/$height); $image=imagescale($image,(int)round($width*$scale),(int)round($height*$scale)); $width=imagesx($image);$height=imagesy($image); }
        ob_start(); if($ext==='jpg') imagejpeg($image,null,88); elseif($ext==='webp') imagewebp($image,null,85); else {imagealphablending($image,false);imagesavealpha($image,true);imagepng($image,null,6);} $bytes=ob_get_clean(); imagedestroy($image);
        Storage::disk('local')->put('blog-media/'.$filename,$bytes);
        $m=Media::create(['user_id'=>$r->user()->id,'filename'=>$filename,'mime'=>$mime,'bytes'=>strlen($bytes),'width'=>$width,'height'=>$height,'alt'=>$d['alt'],'caption'=>$d['caption']??null,'credit'=>$d['credit']??null]);
        return response()->json(['id'=>$m->id,'url'=>$m->url,'alt'=>$m->alt,'caption'=>$m->caption,'width'=>$width,'height'=>$height],201);
    }
    public function show(string $filename) { abort_unless(preg_match('/^[a-f0-9]{40}\.(jpg|png|webp)$/',$filename),404); $m=Media::where('filename',$filename)->firstOrFail();
        if(!auth()->user()?->isStaff()) { $used=\App\Models\Post::eligible()->where(fn($q)=>$q->where('media_id',$m->id)->orWhere('body_html','like','%/media/'.$filename.'%'))->exists(); abort_unless($used,404); }
        $path=Storage::disk('local')->path('blog-media/'.$filename); abort_unless(is_file($path),404);
        return response()->file($path,['Content-Type'=>$m->mime,'X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, max-age=60']);
    }
}
