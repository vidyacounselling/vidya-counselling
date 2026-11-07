@extends('layouts.admin')
@section('title','FAQ details')
@section('actions')<a class="button" href="{{ route('faqs.index') }}">All FAQs</a><a class="button primary" href="{{ route('faqs.edit',$faq) }}">Edit FAQ</a>@endsection
@section('content')
<section class="card"><span class="badge">{{ $faq->status }}</span><h2>{{ $faq->question }}</h2><div class="prose">{!! $faq->answer_html !!}</div></section><section class="card"><h2>Assigned pages</h2>@forelse($faq->pages as $page)<p>{{ $page->title }} · {{ $page->reference }} · Order {{ $page->pivot->display_order }} · <a href="{{ url($page->path) }}" target="_blank" rel="noopener">{{ $page->path }} ↗</a></p>@empty<p>No pages assigned. This FAQ will not appear publicly.</p>@endforelse</section>
@endsection
