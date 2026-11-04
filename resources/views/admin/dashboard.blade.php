@extends('layouts.account')
@section('title', 'Administrator dashboard')
@section('content')
<p class="eyebrow">VIDYA COUNSELLING CENTRE</p>
<h1>Hello, {{ $administrator->name }}.</h1>
<p class="intro">Your administrator account is ready.</p>
<dl>
    <div><dt>Signed in as</dt><dd>{{ $administrator->email }}</dd></div>
    <div><dt>Access</dt><dd>Administrator</dd></div>
    <div><dt>Account created</dt><dd>{{ $administrator->created_at->format('d M Y') }}</dd></div>
    <div><dt>Total accounts</dt><dd>{{ $accountCount }}</dd></div>
    <div><dt>Administrators</dt><dd>{{ $adminCount }}</dd></div>
</dl>
<form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="primary">Sign out</button></form>
@endsection
