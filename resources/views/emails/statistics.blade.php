<p>Hi {{ $user->name }},</p>

<p>For the period of {{ $start->format('j M Y') }} to {{ $end->format('j M Y') }}:</p>

<ul>
	<li>Number of cases imported: {{ number_format($num_cases) }}</li>
	<li>Number of searches for your watchlists: {{ number_format($num_comparisons) }}</li>
	<li>Number of &quot;exact&quot; matches: {{ number_format($num_exact) }}</li>
	<li>Number of &quot;contains&quot; matches: {{ number_format($num_contains) }}</li>
	<li>Total number of matches: {{ number_format($num_exact + $num_contains) }}</li>
</ul>

<p>Regards,<br>Alares</p>
