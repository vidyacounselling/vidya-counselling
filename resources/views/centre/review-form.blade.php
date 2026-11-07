
@extends('layouts.admin')
@section('title',$review->exists?'Moderate Review':'Add consented Review')
@section('description','Publication requires recorded permission. Contact details and moderation notes stay private.')
@include('centre.editor-assets')
@section('actions')<a class="button" href="{{ route('reviews-admin.index') }}">All Reviews</a>
@endsection
@section('content')
<form method="post" action="{{ $review->exists?route('reviews-admin.update',$review):route('reviews-admin.store') }}" class="form-columns" data-dirty>
@csrf 
@if($review->exists) 
@method('PUT') 
@endif<div><section class="card"><h2>Original review</h2>
@if($review->exists)<blockquote class="private-value">{{ $review->original_text }}</blockquote><p class="hint">Immutable original · {{ $review->source }} · {{ $review->created_at->timezone('Asia/Kolkata')->format('d M Y, H:i') }} IST</p>
@else<x-centre-field name="original_text" label="Authentic review text" type="textarea" required/>
@endif<x-centre-field name="public_text" label="Public wording (optional privacy edit)" type="textarea" :value="$review->public_text" hint="Leave blank to show the original. Only privacy/formatting edits; do not change meaning or fabricate praise."/><x-centre-field name="display_name" label="Public display name" :value="$review->display_name"/><x-centre-field name="anonymous" label="Identity preference" type="select" :value="(int)$review->anonymous" :options="[1=>'Anonymous reviewer',0=>'Use explicitly approved display name']"/><x-centre-field name="email" label="Private email" type="email" :value="$review->email" required/><x-centre-field name="service_id" label="Related service" type="select" :value="$review->service_id" :options="[''=>'Not specified']+$services->pluck('name','id')->all()"/><x-centre-field name="rating" label="Original rating (optional)" type="select" :value="$review->rating" :options="[''=>'No rating',1=>'1 out of 5',2=>'2 out of 5',3=>'3 out of 5',4=>'4 out of 5',5=>'5 out of 5']"/></section></div><aside><section class="card"><h2>Moderation & consent</h2><x-centre-field name="status" label="Status" type="select" :value="$review->status" :options="['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','spam'=>'Spam']"/><x-centre-field name="publication_consent" label="Actual publication permission" type="select" :value="(int)$review->publication_consent" :options="[0=>'Not granted / withdrawn',1=>'Granted — source recorded']"/><x-centre-field name="consented_at" label="Actual consent date/time (IST)" type="datetime-local" :value="$review->consented_at?->timezone('Asia/Kolkata')->format('Y-m-d\TH:i')"/><x-centre-field name="moderation_notes" label="Private consent reference / moderation notes" type="textarea" :value="$review->moderation_notes"/><x-centre-field name="featured" label="Featured on homepage" type="select" :value="(int)$review->featured" :options="[0=>'No',1=>'Yes']"/><x-centre-field name="display_order" label="Display order" type="number" :value="$review->display_order??0" required/><button class="button primary">Save moderation</button></section></aside></form>
@endsection
