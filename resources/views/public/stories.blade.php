
@extends('layouts.public')
@section('title','Client Stories')
@section('content')
<section class="page-intro container"><p class="eyebrow">SHARED WITH PERMISSION</p><h1>Every story<br>belongs to someone.</h1><p>We share experiences only with permission. Individual journeys and outcomes vary.</p></section><section class="section container"><div class="cards">
@forelse($stories as $story)
@include('public.story-card')
@empty<div class="empty-public"><h2>Stories, when people are ready.</h2><p>No client stories are published at present. Privacy always comes first.</p></div>
@endforelse</div>{{ $stories->links() }}</section>
@endsection
