@extends('layouts.account')
@section('title', 'First administrator setup')
@section('content')
<p class="eyebrow">SECURE FIRST ACCOUNT SETUP</p>
<h1>A thoughtful beginning.</h1>
<p class="intro">Create the centre’s first administrator account using your privately supplied setup token.</p>
@if ($errors->any())
<div class="errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form method="post" action="{{ route('register') }}">
    @csrf
    <label for="name">Your name</label><input id="name" name="name" autocomplete="name" required maxlength="120" value="{{ old('name') }}">
    <label for="email">Email address <small>(lowercase)</small></label><input id="email" name="email" type="email" autocomplete="username" required maxlength="254" value="{{ old('email') }}">
    <label for="password">Password</label>
    <div class="password-field"><input id="password" name="password" type="password" autocomplete="new-password" required minlength="12" maxlength="128" aria-describedby="password-hint"><button type="button" data-toggle-password="password" aria-label="Show password" aria-pressed="false">Show</button></div>
    <p class="hint" id="password-hint">At least 12 characters with uppercase and lowercase letters, a number and a symbol.</p>
    <label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required maxlength="128">
    <label for="setup_token">Private setup token</label><input id="setup_token" name="setup_token" type="password" autocomplete="off" spellcheck="false" autocapitalize="none" required minlength="64" maxlength="64" aria-describedby="token-hint">
    <p class="hint" id="token-hint">Single use and time limited. Enter it here; never add it to the page URL.</p>
    <button class="primary" type="submit">Create administrator <span aria-hidden="true">↗</span></button>
</form>
@endsection
