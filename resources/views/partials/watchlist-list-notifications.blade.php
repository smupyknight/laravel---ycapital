@foreach ($notifications as $notification)
    <tr style="display:none">
        <td>{{ $notification->created_at->setTimezone(Auth::user()->timezone)->format('j/m/Y g:i A') }} ({{ $notification->created_at->diffForHumans() }})</td>
        <td>

			@if ($notification->entity->party_name)
				{{ $notification->entity->party_name.' ' }}
			@endif

			@if ($notification->entity->abn)
				{{ $notification->entity->abn.' ' }}
			@endif

			@if ($notification->entity->acn)
				{{ $notification->entity->acn.' ' }}
			@endif

		</td>
        <td><a href="/client/watchlists/manage/{{ $notification->entity->watchlist->id }}">{{ $notification->entity->watchlist->name }}</a></td>
        <td><a target="_blank" href="/client/cases/view/{{ $notification->case_id }}"><button class="btn btn-xs btn-primary">View Case</button></a></td>
    </tr>
@endforeach
