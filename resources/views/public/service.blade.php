
@extends('layouts.public')
@section('title',$service->seo_title?:$service->name)
@section('description',$service->seo_description?:$service->summary)
@section('canonical',$service->canonical)
@section('robots',$service->noindex?'noindex,follow':'index,follow')
@section('ogtitle',$service->og_title?:$service->name)
@section('ogdescription',$service->og_description?:$service->summary)
@section('ogimage',$service->og_image?:($service->media?->url?:asset('images/vidya-logo.png')))
@push('head')<script type="application/ld+json">{!! app(\App\Services\CentreSchema::class)->encode($schema['data']) !!}</script>
@endpush
@section('content')
<section class="page-intro container"><nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a> / <a href="{{ route('services') }}">Services</a></nav><p class="eyebrow">SPACE FOR MEANINGFUL CHANGE</p><h1>{{ $service->name }}</h1><p>{{ $service->summary }}</p><a class="btn" href="{{ route('contact',['service'=>$service->id]) }}">{{ $service->cta_label }} ↗</a></section><section class="section container detail-grid"><div class="prose">
@if($service->media)<img src="{{ $service->media->url }}" alt="{{ $service->media->alt }}">
@endif{!! $service->body_html !!}
@foreach(['audience'=>'Who this is for','concerns'=>'What we can explore','expect'=>'What to expect','modes'=>'Ways to connect'] as $kind=>$heading)
@if($service->facts($kind)->isNotEmpty())<h2>{{ $heading }}</h2><ul>
@foreach($service->facts($kind) as $fact)<li>
@if($fact->heading)<strong>{{ $fact->heading }}</strong> 
@endif{{ $fact->content }}</li>
@endforeach</ul>
@endif 
@endforeach</div><aside class="info-card"><p class="eyebrow">YOUR NEXT STEP</p><h2>Start with a conversation.</h2><p>Ask about suitability and availability. An inquiry does not confirm an appointment.</p>
@if($service->offer_verified&&($service->fees||$service->duration))<dl>
@if($service->duration)<dt>Session duration</dt><dd>{{ $service->duration }}</dd>
@endif 
@if($service->fees)<dt>Fees</dt><dd>{{ $service->fees }}</dd>
@endif</dl>
@endif<a class="btn" href="{{ route('contact',['service'=>$service->id]) }}">Request a Consultation ↗</a></aside></section>
<section class="section container"><div class="section-heading"><p class="eyebrow">EXPLORE FURTHER</p><h2>Other ways we can support you.</h2></div>
@include('public.service-cards',['services'=>$services->where('id','!=',$service->id)])</section>
@endsection
