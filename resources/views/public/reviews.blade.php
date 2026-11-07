
@extends('layouts.public')
@section('title','Reviews')
@section('content')
<section class="page-intro container"><p class="eyebrow">WORDS SHARED WITH CARE</p><h1>Experiences.<br>In their own words.</h1><p>Published with permission and reviewed for privacy. Individual experiences vary.</p></section><section class="section container"><div class="cards">
@forelse($reviews as $review)
@include('public.review-card')
@empty<div class="empty-public"><h2>A space for honest experiences.</h2><p>No reviews are published at present.</p></div>
@endforelse</div>{{ $reviews->links() }}</section><section class="section container form-section"><div><p class="eyebrow">SHARE YOUR EXPERIENCE</p><h2>Your words.<br>Your choice.</h2><p>Choose an anonymous display if you prefer. Do not include identifying clinical details, information about other people, or contact details in your review.</p><p>Every submission is moderated. Sending a review does not guarantee publication.</p></div><form method="post" action="{{ route('reviews.submit') }}" data-private-form class="public-form">
@csrf<input type="hidden" name="submission_key" value="{{ $formToken['submission_key'] }}"><input type="hidden" name="form_signature" value="{{ $formToken['form_signature'] }}"><div class="hp" aria-hidden="true"><input name="website" tabindex="-1" aria-label="Leave empty"></div><div class="form-result" role="status" tabindex="-1" hidden></div><label>Display preference<select name="anonymous"><option value="1">Publish anonymously</option><option value="0">Use my display name</option></select></label><label>Display name (required if identified)<input name="display_name" maxlength="100" autocomplete="off"></label><label>Email (private, never displayed)<input type="email" name="email" required maxlength="254" autocomplete="email"></label><label>Service (optional)<select name="service_id"><option value="">Prefer not to specify</option>
@foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>
@endforeach</select></label><label>Rating (optional)<select name="rating"><option value="">No rating</option>
@foreach(range(5,1) as $n)<option value="{{ $n }}">{{ $n }} out of 5</option>
@endforeach</select></label><label>Your experience<textarea name="review_text" required minlength="10" maxlength="3000" rows="5"></textarea></label><label class="consent"><input type="checkbox" name="publication_consent" value="1" required><span>I consent to publication of this review using my selected display preference, subject to moderation. My email stays private. <a href="{{ route('privacy') }}">Privacy notice</a></span></label><button class="btn">Submit for review ↗</button></form></section>
@endsection
