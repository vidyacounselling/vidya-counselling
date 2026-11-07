
@extends('layouts.admin')
@section('title','Consultation Inquiries')
@section('description','Private workspace. Encrypted contact details and messages are restricted to authorised staff.')
@section('content')
<form method="get" class="card filters"><label>Status<select name="status"><option value="">All current</option>
@foreach(['new','contacted','in_progress','closed','spam','trash'] as $v)<option 
@selected(request('status')===$v)>{{ $v }}</option>
@endforeach</select></label><label>Assigned staff<select name="assigned_to"><option value="">Any staff</option>
@foreach($staff as $person)<option value="{{ $person->id }}" 
@selected(request('assigned_to')==$person->id)>{{ $person->name }}</option>
@endforeach</select></label><label>Reference<input name="reference" value="{{ request('reference') }}" placeholder="VCC-…" maxlength="20"></label>
@foreach(['interest'=>['Mental Health','Marriage and Family Counseling'],'session_preference'=>['In-person','Online','No preference / Help me decide'],'source'=>['consultation_form','lets_talk']] as $key=>$choices)<label>{{ ucfirst(str_replace('_',' ',$key)) }}<select name="{{ $key }}"><option value="">Any</option>
@foreach($choices as $choice)<option 
@selected(request($key)===$choice)>{{ $choice }}</option>
@endforeach</select></label>
@endforeach<label>From (IST)<input type="date" name="from" value="{{ request('from') }}"></label><label>To (IST)<input type="date" name="to" value="{{ request('to') }}"></label><button class="button">Apply filters</button><a href="{{ route('inquiries.index') }}">Reset</a></form><p class="hint">{{ $items->total() }} matching record(s). Contact details are not displayed in this list.</p><section class="card table-card"><div class="table-scroll" tabindex="0" role="region" aria-label="Scrollable records table"><table><thead><tr><th>Reference</th><th>Interest / mode</th><th>Status</th><th>Assigned</th><th>Received</th><th>Action</th></tr></thead><tbody>
@forelse($items as $inquiry)<tr><td><strong>{{ $inquiry->reference }}</strong><small class="muted">{{ $inquiry->source }}</small></td><td>{{ $inquiry->interest }}<br><small>{{ $inquiry->session_preference }}</small></td><td><span class="badge">{{ $inquiry->trashed()?'archived':$inquiry->status }}</span></td><td>{{ $inquiry->assignee?->name??'Unassigned' }}</td><td>{{ $inquiry->created_at->timezone('Asia/Kolkata')->format('d M Y, H:i') }} IST</td><td>
@if($inquiry->trashed())
@if(auth()->user()->isAdministrator())<form method="post" action="{{ route('inquiries.restore',$inquiry->id) }}">
@csrf<button class="button small">Restore</button></form>
@endif 
@else<a href="{{ route('inquiries.show',$inquiry) }}">Open private record →</a>
@endif</td></tr>
@empty<tr><td colspan="6"><div class="empty-state"><h2>No matching inquiries</h2><p>Public consultation and Let’s Talk submissions appear here after consent.</p></div></td></tr>
@endforelse</tbody></table></div>{{ $items->links() }}</section><p class="hint">Retention requires manual review. Use the record’s Administrator-only redaction action when appropriate; it preserves a non-identifying operational record.</p>
@endsection
