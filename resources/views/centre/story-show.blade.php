@extends('layouts.admin')
@section('title','Client Story details')
@section('actions')<a class="button" href="{{ route('stories.index') }}">All Stories</a><a class="button primary" href="{{ route('stories.edit',$story) }}">Edit & consent record</a>@endsection
@section('content')
<section class="card"><span class="badge">{{ $story->status }} · Consent {{ $story->consent_status }}</span><h2>{{ $story->title }}</h2><blockquote>{{ $story->quotation }}</blockquote><p>Public attribution: {{ $story->public_name }}</p><div class="prose">{!! $story->story_html !!}</div></section><section class="card"><h2>Private source reference</h2><p class="private-value">{{ $story->private_notes }}</p></section>
@endsection
