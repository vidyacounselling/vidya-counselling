@extends('layouts.account')
@section('title', 'Administrator sign in')
@section('content')
<p class="eyebrow">PRIVATE ADMINISTRATION</p>
<h1>Welcome back.</h1>
<p class="intro">Sign in to your centre account.</p>
@if ($errors->any())
<div class="errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form method="post" action="{{ route('login') }}">
    @csrf
    <label for="email">Email address</label>
    <input id="email" name="email" type="email" autocomplete="username" required maxlength="254" value="{{ old('email') }}">
    <label for="password">Password</label>
    <div class="password-field"><input id="password" name="password" type="password" autocomplete="current-password" required maxlength="128"><button type="button" data-toggle-password="password" aria-label="Show password" aria-pressed="false">Show</button></div>
    <button class="primary" type="submit">Sign in <span aria-hidden="true">↗</span></button>
</form>
@endsection
