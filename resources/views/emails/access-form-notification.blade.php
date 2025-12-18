@php $reqs = $requests ?? []; @endphp

<h2>Data Access Request</h2>

<p><strong>Name:</strong> {{ $full_name }}</p>
<p><strong>Email:</strong> {{ $email }}</p>
@if(!blank($mobile)) <p><strong>Mobile:</strong> {{ $mobile }}</p> @endif
@if(!blank($agreement_number)) <p><strong>Agreement #:</strong> {{ $agreement_number }}</p> @endif

@if(!empty($reqs))
  <p><strong>Request type(s):</strong> {{ implode(', ', $reqs) }}</p>
@endif

<p><strong>Preferred contact:</strong> {{ ucfirst($prefer_contact ?? 'email') }}</p>

<p><strong>Details:</strong><br>{!! nl2br(e($description)) !!}</p>

<hr>
<p><small>IP: {{ $ip }} | UA: {{ $ua }}</small></p>
<p><small>Attachments (if any) are included with this email.</small></p>
