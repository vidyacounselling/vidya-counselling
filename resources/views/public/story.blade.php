@extends('layouts.public')
@section('title','Our Story')
@section('content')
<section class="page-intro container"><p class="eyebrow">THE HEART BEHIND VIDYA</p><h1>A purpose rooted<br>in understanding.</h1><p>{{ $website['founder_intro'] }}</p></section><section class="section container detail-grid"><article class="prose"><h2>{{ $website['founder_name'] }}</h2><p class="eyebrow">FOUNDER, VIDYA COUNSELING CENTRE</p>{!! $website['story_html'] !!}</article><aside>@if($portrait)<figure><img class="founder-portrait" src="{{ $portrait->url }}" alt="{{ $portrait->alt }}"><figcaption>{{ $website['founder_name'] }}</figcaption></figure>@endif<div class="story-mark"><span>Vidya</span><p>Named for a mother.<br>Built to support others.</p><div aria-hidden="true">✳</div></div></aside></section>
@endsection
