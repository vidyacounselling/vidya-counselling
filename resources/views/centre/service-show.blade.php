
@extends('layouts.admin')
@section('title','Service details')
@section('actions')<a class="button" href="{{ route('services-admin.index') }}">All Services</a><a class="button primary" href="{{ route('services-admin.edit',$service) }}">Edit Service</a>
@endsection
@section('content')
<section class="card"><span class="badge">{{ $service->status }}</span><h2>{{ $service->name }}</h2><p>{{ $service->summary }}</p><p>Current URL: {{ $service->public_url }}</p><p>Canonical: {{ $service->canonical }}</p><p>Schema source: <strong>{{ $schema['source'] }}</strong></p><div class="prose">{!! $service->body_html !!}</div></section><section class="card"><h2>Effective JSON-LD</h2><p class="hint">This is the Service + breadcrumb output. The shared public layout emits the actual centre Organization separately; footer credits are not the service provider.</p><pre class="private-value">{{ json_encode($schema['data'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</pre></section>
@endsection
