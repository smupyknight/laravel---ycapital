@extends('layouts.client_new')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
        	<h2>Watchlists <button type="button" class="btn btn-primary btn-md pull-right new-watchlist">New Watchlist</button></h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>
                            #
                        </th>
                        <th>
                            Name
                        </th>
                        <th>
                            Last Alert
                        </th>
                        <th>
                            Total Alerts
                        </th>
                        <th>
                            Total Entities
                        </th>
                        <th>
                        	Total Subscribers
                        </th>
                        <th>
                        	Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($watchlists))
                    <?php
                    $total_alerts = 0;
                    $total_entities = 0;
                    $total_subscribers = 0;
                    ?>
                    @foreach ($watchlists as $key => $watchlist)
                        <?php
                            $alerts = $watchlist->notifications()->count();
                            $entities = count($watchlist->entities);
                            $subscribers = count($watchlist->subscribers);

                            $total_alerts += $alerts;
                            $total_entities += $entities;
                            $total_subscribers += $subscribers;
                        ?>
                        <tr>
                            <th scope="row">
                                {{ $key + 1 }}
                            </th>
                            <td>
                                {{ $watchlist->name }}
                            </td>
                            <td>
                                @if ($time = $watchlist->getLatestNotificationTime())
                                    {{ $time->setTimezone(Auth::user()->timezone)->format('j/m/Y g:i A') }} ({{ $time->diffForHumans() }})
                                @endif
                            </td>
                            <td>
                                {{ $alerts }} Alerts
                            </td>
                            <td>
                                {{ $entities }}
                            </td>
                            <td>
                                {{ $subscribers }}
                            </td>
                            <td>
                            	<a href="/client/watchlists/manage/{{ $watchlist->id }}"><button type="button" class="btn btn-default btn-xs">Manage</button></a>
                                <button type="button" onclick="edit_watchlist('{{$watchlist->id}}','{{addslashes($watchlist->name)}}');return false;" class="btn btn-default btn-xs">Edit</button>
                                <button type="button" onclick="delete_watchlist('{{$watchlist->id}}');return false;" class="btn btn-default btn-xs">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                        <tr>
                            <td colspan="3" class="text-right"><b>Totals:</b></td>
                            <td>{{ $total_alerts }}</td>
                            <td>{{ $total_entities }}</td>
                            <td>{{ $total_subscribers }}</td>
                            <td></td>
                        </tr>
                    @else
                        <tr>
                            <th colspan="7" style="text-align:center">
                                No Watchlists Yet.
                            </th>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
   <div class="row">
        <div class="col-lg-12">
            <h2>Recent Notifications</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Match On</th>
                        <th>Watchlist</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="notification_body">
                    @include('partials.watchlist-list-notifications',['notifications' => $notifications])
                </tbody>
                <tfoot>
                    <tr>
                        <input type="hidden" id="show_more_counter" value="10">
                        <td colspan="4" class="text-center"><a href="#" onclick="show_more_notifications(this);return false;">Show More</a></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    function edit_watchlist(id,name)
    {
        var edit_watchlist_modal = ''+
        '<form action="/client/watchlists/edit/'+id+'" method="POST" class="form-horizontal">'+
            '<div class="form-group">'+
                '<label class="col-md-4 control-label">Name</label>'+
                '<div class="col-md-8">'+
                    '<input type="text" name="name" id="watchlist_name" class="form-control">'+
                '</div>'+
            '</div>'+
            '{{ csrf_field() }}'+
        '</form>';
        modalform.dialog({
            bootbox : {
                title : 'Edit Watchlist',
                message : edit_watchlist_modal,
                buttons : {
                    submit: {
                        label: 'Save',
                        className: 'btn-primary'
                    },
                    cancel: {
                        label: 'Cancel',
                        className: 'btn-default'
                    }
                }
            },
            after_init : function(){
                $('#watchlist_name').val(name);
            }
        });
    }

    function delete_watchlist(id)
    {
        var delete_watchlist_modal = ''+
        '<form action="/client/watchlists/delete/'+id+'" method="POST" class="form-horizontal">'+
            'Are you sure you want to delete this watchlist?'+
            '{{ csrf_field() }}'+
        '</form>';
        modalform.dialog({
            bootbox : {
                title : 'Delete Watchlist',
                message : delete_watchlist_modal,
                buttons : {
                    cancel: {
                        label: 'Cancel',
                        className: 'btn-default'
                    },
                    submit: {
                        label: 'Delete',
                        className: 'btn-primary'
                    },
                }
            },
        });
    }
    function show_more_notifications(selector)
    {
        $(selector).html('Loading, please wait..');

        var counter = $('#show_more_counter').val();
        $('#show_more_counter').val((counter * 1) + 10);
        $.ajax({
          url: '/client/watchlists/more-notifications-all/' + counter,
          type: "get",
          success: function(data){
                $('#notification_body').append(data.html);
                $('#notification_body > tr').fadeIn();
            if (data.show_more) {
                $(selector).html('Show More');
            } else {
                $(selector).parent().remove();
            }
          }
        });
    }

    $(document).ready(function(){
        $('#notification_body > tr').show();

        $('.new-watchlist').click(function(){
            var add_watchlist_modal = ''+
            '<form action="/client/watchlists/add" method="POST" class="form-horizontal">'+
                '<div class="form-group">'+
                    '<label class="col-md-4 control-label">Name</label>'+
                    '<div class="col-md-8">'+
                        '<input type="text" name="name" class="form-control">'+
                    '</div>'+
                '</div>'+
                '{{ csrf_field() }}'+
            '</form>';
            modalform.dialog({
                bootbox : {
                    title : 'Add Watchlist',
                    message : add_watchlist_modal,
                    buttons : {
                        submit: {
                            label: 'Save',
                            className: 'btn-primary'
                        },
                        cancel: {
                            label: 'Cancel',
                            className: 'btn-default'
                        }
                    }
                },
            });
        });
    });
</script>
@endsection
