
@extends('layouts.admin')
@section('title','FAQ draft helper')
@section('description','Template-based suggestions from actual page facts. Not a generative AI service.')
@include('centre.editor-assets')
@section('content')
<section class="card"><h2>{{ $page->title }}</h2><p class="muted">Edit selected suggestions before saving as drafts. Empty answers need verified facts. Nothing is automatically published; existing and similar questions are excluded.</p>
@if(count($suggestions))<form method="post" action="{{ route('faqs.accept') }}">
@csrf<input type="hidden" name="page_id" value="{{ $page->id }}">
@foreach($suggestions as $i=>$item)<section class="section-row"><label class="check"><input type="checkbox" name="selected[]" value="{{ $i }}"> Select this draft</label><label>Question<input name="suggestions[{{ $i }}][question]" value="{{ $item['question'] }}" maxlength="500" required></label><label>Draft answer<textarea name="suggestions[{{ $i }}][answer]" maxlength="5000" rows="4">{{ $item['answer'] }}</textarea></label></section>
@endforeach<button class="button primary">Save selected as drafts</button></form>
@else<p>No new template suggestions remain. Create a manual FAQ or assign an existing question.</p>
@endif<a href="{{ route('faqs.index') }}">Back to FAQs</a></section>
@endsection
