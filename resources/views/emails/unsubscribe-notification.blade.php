@php
$reasons = $reasons ?? [];
@endphp


<h2>Unsubscribe Request</h2>
<p><strong>Name:</strong> {{ $full_name }}</p>
<p><strong>Email:</strong> {{ $email }}</p>
@if(!blank($mobile))
<p><strong>Mobile:</strong> {{ $mobile }}</p>
@endif
@if(!empty($reasons))
<p><strong>Reason(s):</strong> {{ implode(', ', $reasons) }}</p>
@endif
@if(!blank($message))
<p><strong>Message:</strong><br>{{ nl2br(e($message)) }}</p>
@endif
<hr>
<p><small>IP: {{ $ip }} | UA: {{ $ua }}</small></p>