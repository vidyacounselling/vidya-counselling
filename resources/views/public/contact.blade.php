
@extends('layouts.public')
@section('title','Request a Consultation')
@section('content')
<section class="page-intro container"><p class="eyebrow">WHEN YOU’RE READY</p><h1>Let’s take the<br>first step together.</h1><p>Tell us how to reach you. The centre will respond to discuss your request and availability.</p></section><section class="section container form-section"><aside><h2>A space to begin.</h2><p>This is a private inquiry, not a confirmed appointment. Please avoid sensitive clinical information in this initial message.</p>
@if(($website['contact_verified']??'0')==='1')<div class="contact-detail"><h3>Call or email</h3><a href="tel:{{ $website['phone'] }}">{{ $website['phone'] }}</a><br><a href="mailto:{{ $website['email'] }}">{{ $website['email'] }}</a><h3>Visit us</h3><p>{{ $website['address'] }}</p><a class="text-link" href="{{ $website['map_url'] }}" target="_blank" rel="noopener noreferrer">Open map ↗</a></div>
@endif<p class="form-note">This form is not monitored for emergencies. If you are in immediate danger, contact local emergency services.</p></aside><div class="public-form">
@if($errors->any())<div class="notice" role="alert"><ul>
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach</ul></div>
@endif 
@include('public.inquiry-form',['widget'=>false])</div></section>
@endsection
