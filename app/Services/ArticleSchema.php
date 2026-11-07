<?php
namespace App\Services;
use App\Models\Post;
use Illuminate\Validation\ValidationException;

class ArticleSchema {
    public function manual(?string $json, Post $post): ?array {
        if(!trim($json??'')) return null;
        try { $data=json_decode($json,true,32,JSON_THROW_ON_ERROR); }
        catch(\Throwable $e) { throw ValidationException::withMessages(['manual_schema'=>'Enter valid JSON data (no script tags). '.$e->getMessage()]); }
        if(!is_array($data)||strlen($json)>40000) $this->fail('Use a JSON object, array or @graph under 40 KB.');
        $nodes=isset($data['@graph'])?$data['@graph']:(array_is_list($data)?$data:[$data]);
        $articles=0; $breadcrumbs=0; $ids=[];
        foreach($nodes as $node) {
            if(!is_array($node)) $this->fail('Every schema node must be an object.');
            if(isset($node['@context']) && $node['@context']!=='https://schema.org' && $node['@context']!=='https://schema.org/') $this->fail('Use https://schema.org as context.');
            $types=(array)($node['@type']??[]);
            if(array_intersect($types,['Article','BlogPosting','NewsArticle'])) {
                $articles++;
                if(($node['headline']??null)!==$post->title) $this->fail('Article headline must match the visible title.');
                $main=$node['mainEntityOfPage']??null;
                $url=$node['url']??(is_array($main)?($main['@id']??null):$main);
                if($url!==$post->canonical) $this->fail('Article URL must match the canonical URL.');
                $author=$node['author']??null;
                if(is_array($author)&&isset($author['name'])&&$author['name']!==$post->author->name)$this->fail('Schema author must match the stored article author.');
                foreach(['datePublished'=>'published_at','dateModified'=>'content_updated_at'] as $property=>$field)if(isset($node[$property])&&$post->$field){try{$date=\Illuminate\Support\Carbon::parse($node[$property]);}catch(\Throwable){$this->fail('Use a valid ISO date for '.$property);}if(!$date->equalTo($post->$field))$this->fail($property.' must match the stored article date.');}
            }
            if(in_array('BreadcrumbList',$types,true)) $breadcrumbs++;
            if(isset($node['@id'])) { if(in_array($node['@id'],$ids,true)) $this->fail('Duplicate @id values are not allowed.'); $ids[]=$node['@id']; }
        }
        if($articles!==1 || $breadcrumbs>1) $this->fail('Provide exactly one Article/BlogPosting node and at most one BreadcrumbList.');
        return $data;
    }
    private function fail(string $message): never { throw ValidationException::withMessages(['manual_schema'=>$message]); }
    public function effective(Post $post): array {
        $manual=$post->schema_mode==='manual'?$this->manual($post->manual_schema,$post):null;
        $breadcrumb=['@context'=>'https://schema.org','@type'=>'BreadcrumbList','@id'=>$post->canonical.'#breadcrumb','itemListElement'=>[
            ['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>url('/')],['@type'=>'ListItem','position'=>2,'name'=>'Blog','item'=>url('/blog')],['@type'=>'ListItem','position'=>3,'name'=>$post->title,'item'=>$post->canonical]]];
        if($manual) { $nodes=$manual['@graph']??(array_is_list($manual)?$manual:[$manual]); $has=false; foreach($nodes as $node) if(in_array('BreadcrumbList',(array)($node['@type']??[]),true)) $has=true; return ['source'=>'Manual','data'=>$has?$manual:['@context'=>'https://schema.org','@graph'=>[...$nodes,$breadcrumb]]]; }
        $data=['@context'=>'https://schema.org','@type'=>'BlogPosting','@id'=>$post->canonical.'#article','headline'=>$post->title,'description'=>$post->description,'url'=>$post->canonical,'mainEntityOfPage'=>['@type'=>'WebPage','@id'=>$post->canonical],
            'author'=>['@type'=>'Person','name'=>$post->author->name],'publisher'=>['@type'=>'Organization','name'=>'Vidya Counseling Centre','url'=>url('/'),'logo'=>['@type'=>'ImageObject','url'=>asset('images/vidya-logo.png')]],'inLanguage'=>$post->language];
        if($post->published_at) $data['datePublished']=$post->published_at->toIso8601String();
        if($post->content_updated_at) $data['dateModified']=$post->content_updated_at->toIso8601String();
        if($post->media) $data['image']=[$post->media->url];
        return ['source'=>'Automatic','data'=>['@context'=>'https://schema.org','@graph'=>[$data,$breadcrumb]]];
    }
    public function encoded(Post $post): string { return json_encode($this->effective($post)['data'],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR); }
}
