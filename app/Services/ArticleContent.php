<?php
namespace App\Services;

use Symfony\Component\HtmlSanitizer\{HtmlSanitizer,HtmlSanitizerConfig};

class ArticleContent
{
    public function sanitize(string $html): string
    {
        $config=(new HtmlSanitizerConfig())->withMaxInputLength(500000)->allowSafeElements()
            ->allowElement('h2',['id'])->allowElement('h3',['id'])->allowElement('h4',['id'])
            ->allowElement('figure',[])->allowElement('figcaption',[])->allowElement('hr',[])
            ->allowElement('img',['src','alt','width','height','loading'])
            ->allowElement('a',['href','title','target','rel'])->allowLinkSchemes(['https','http','mailto'])
            ->allowMediaSchemes(['https'])->allowRelativeMedias()->allowRelativeLinks()
            ->forceAttribute('a','rel','nofollow noopener noreferrer')->forceAttribute('img','loading','lazy')
            ->dropElement('script')->dropElement('style')->dropElement('iframe')->dropElement('object')->dropElement('form')->dropElement('h1');
        $clean=(new HtmlSanitizer($config))->sanitize($html);
        $dom=new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="article-root">'.$clean.'</div>', LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
        foreach(iterator_to_array($dom->getElementsByTagName('img')) as $image) {
            $path=parse_url($image->getAttribute('src'),PHP_URL_PATH);
            $host=parse_url($image->getAttribute('src'),PHP_URL_HOST);
            if (($host && $host!==parse_url(config('app.url'),PHP_URL_HOST)) || !preg_match('~^/media/([a-f0-9]{40}\.(?:jpg|png|webp))$~',$path??'', $m) || !($media=\App\Models\Media::where('filename',$m[1])->first())) {
                $image->parentNode->removeChild($image); continue;
            }
            $image->setAttribute('src',$media->url); $image->setAttribute('width',(string)$media->width); $image->setAttribute('height',(string)$media->height);
        }
        $n=0;
        foreach((new \DOMXPath($dom))->query('//h2|//h3|//h4') as $heading) $heading->setAttribute('id','section-'.(++$n));
        $root=$dom->getElementById('article-root'); $out='';
        if($root) foreach($root->childNodes as $child) $out.=$dom->saveHTML($child);
        libxml_clear_errors();
        return $out;
    }
    public function toc(string $html): array {
        preg_match_all('~<h([234]) id="([^"]+)">(.*?)</h[234]>~su',$html,$matches,PREG_SET_ORDER);
        return array_map(fn($m)=>['level'=>$m[1],'id'=>$m[2],'text'=>html_entity_decode(strip_tags($m[3]))],$matches);
    }
}
