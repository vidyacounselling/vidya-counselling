
@if(isset($assignedFaqs)&&$assignedFaqs->isNotEmpty())<section class="section container faqs"><div class="section-heading"><p class="eyebrow">A LITTLE CLARITY</p><h2>Questions you may have</h2></div><div class="faq-list">
@foreach($assignedFaqs as $faq)<details><summary>{{ $faq->question }}<span aria-hidden="true">+</span></summary><div class="prose">{!! $faq->answer_html !!}</div></details>
@endforeach</div></section>
@if(($website['faq_schema_enabled']??'0')==='1'&&$currentPublicPage)
@php($canonicalFaqs=$assignedFaqs->where('schema_page_id',$currentPublicPage->id))
@if($canonicalFaqs->isNotEmpty())<script type="application/ld+json">{!! app(\App\Services\CentreSchema::class)->encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>$canonicalFaqs->map(fn($f)=>['@type'=>'Question','name'=>$f->question,'acceptedAnswer'=>['@type'=>'Answer','text'=>strip_tags($f->answer_html)]])->values()->all()]) !!}</script>
@endif
@endif 
@endif
