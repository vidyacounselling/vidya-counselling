
@extends('layouts.admin')
@section('title','Private Inquiry')
@section('description','Sensitive contact information. Use only to respond to this request.')
@include('centre.editor-assets')
@section('actions')<a class="button" href="{{ route('inquiries.index') }}">All Inquiries</a>
@endsection
@section('content')
<div class="form-columns"><div><section class="card"><span class="badge">{{ $inquiry->status }}</span><h2>{{ $inquiry->reference }}</h2><dl><dt>Full name</dt><dd class="private-value">{{ $inquiry->full_name }}</dd><dt>Email</dt><dd class="private-value">{{ $inquiry->email }}</dd><dt>Mobile</dt><dd>{{ $inquiry->mobile }}</dd><dt>Area of interest</dt><dd>{{ $inquiry->interest }}</dd><dt>Mode</dt><dd>{{ $inquiry->session_preference }}</dd><dt>Related service</dt><dd>{{ $inquiry->service?->name??'Not specified' }}</dd><dt>Contact preference</dt><dd>{{ $inquiry->contact_method }} · {{ $inquiry->preferred_window?:'No window specified' }}</dd><dt>Message</dt><dd class="private-value">{{ $inquiry->message?:'No message provided' }}</dd><dt>Source / received</dt><dd>{{ $inquiry->source }} · {{ $inquiry->created_at->timezone('Asia/Kolkata')->format('d M Y, H:i') }} IST</dd><dt>Consent record</dt><dd>{{ $inquiry->consent_version }} · {{ $inquiry->consented_at->timezone('Asia/Kolkata')->format('d M Y, H:i') }} IST</dd></dl></section><section class="card"><h2>Private follow-up history</h2>
@forelse($inquiry->followups as $followup)<article><p><strong>{{ $followup->action }}</strong> · {{ $followup->user?->name }} · {{ $followup->created_at->timezone('Asia/Kolkata')->format('d M Y, H:i') }} IST</p><p class="private-value">{{ $followup->note }}</p><hr></article>
@empty<p>No follow-up recorded.</p>
@endforelse</section></div><aside><form class="card" method="post" action="{{ route('inquiries.update',$inquiry) }}">
@csrf 
@method('PUT')<h2>Follow-up</h2><x-centre-field name="status" label="Status" type="select" :value="$inquiry->status" :options="['new'=>'New','contacted'=>'Contacted','in_progress'=>'In progress','closed'=>'Closed','spam'=>'Spam']"/><x-centre-field name="assigned_to" label="Authorised staff" type="select" :value="$inquiry->assigned_to" :options="[''=>'Unassigned']+$staff->pluck('name','id')->all()"/><x-centre-field name="note" label="Private follow-up note" type="textarea" hint="Encrypted; avoid unnecessary sensitive details."/><button class="button primary">Save follow-up</button></form>
@if(auth()->user()->isAdministrator())<section class="card"><h2>Retention actions</h2><form method="post" action="{{ route('inquiries.destroy',$inquiry) }}" data-confirm="Archive this inquiry? It can be restored.">
@csrf 
@method('DELETE')<button class="button">Archive record</button></form><hr><p class="hint">Permanent redaction removes contact details, message and follow-up notes. Operational reference/status remain; backup retention is separate.</p><form method="post" action="{{ route('inquiries.redact',$inquiry) }}" data-confirm="Permanently redact this inquiry’s personal data? This action cannot be undone in the application.">
@csrf<label>Type REDACT to confirm<input name="confirm" required pattern="REDACT" autocomplete="off"></label><button class="button danger">Redact personal data</button></form></section>
@endif</aside></div>
@endsection
