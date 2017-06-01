<!DOCTYPE html>
<html>
<body style="margin:0; padding:0;">
<div text="#000000" style="background-color:#ededed;width:100%!important">
    <div>
        <table style="font-family:Helvetica,Arial,sans-serif" border="0" cellspacing="0" cellpadding="0" width="100%" align="center" bgcolor="#ededed">
            <tbody>
                <tr>
                    <td>
                        <table style="font-size:11px;line-height:14px;color:#666666" border="0" cellspacing="0" cellpadding="0" width="600" align="center">
                            <tbody>
                                <tr>

                            </tbody>
                        </table><br/>
                        <table style="font-size:13px;line-height:18px;color:#666666;border-radius:0px;border:#ccc 1px solid" border="0" cellspacing="0" cellpadding="0" width="600" align="center" bgcolor="#ffffff">
                            <tbody>
                                <tr>
                                    <td style="padding:15px 30px; background: #4882c3;background: -webkit-linear-gradient(-90deg, #4882c3, #5c97d8);background: -o-linear-gradient(-90deg, #4882c3, #5c97d8);background: -moz-linear-gradient(-90deg, #4882c3, #5c97d8);background: linear-gradient(-90deg, #4882c3, #5c97d8);">
                                        <table style="font-size:13px;color:#666666" border="0" cellspacing="0" cellpadding="0" width="600" align="center">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <p style="margin:0;">
                                                            <a href="#" target="_blank"><img src="{{url('alares.png')}}" alt="Alares" style="display:block; width:100%; height:auto; max-width:96px;"></a>
                                                        </p>
                                                    </td>
                                                    <td width="200" style="vertical-align: top;">
                                                        <p style="color:#fff; margin:0; float:right;">
                                                            <span style="display:inline-block; width:25px; height:25px; background-color:#fff; border-radius:50%;"></span>
                                                            <span style="display:inline-block; vertical-align: top; line-height:25px;"> {{ ucfirst($subscriber->name) }} </span>
                                                        </p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table style="font-size:13px;line-height:18px;color:#666666" border="0" cellspacing="0" cellpadding="0" width="600" align="center">
                                            <tbody>
                                                <tr>
                                                    <td style="line-height:0;font-size:0" colspan="3" height="20">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td width="40">&nbsp;</td>
                                                    <td>
                                                        <h2 style="font-size: 24px; font-weight: 400; margin: 5px 0px; color:#5189c6; text-align:center;">WATCHLIST NOTIFICATIONS</h2>
                                                    </td>
                                                    <td width="40">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="line-height:0;font-size:0" colspan="3" height="40">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td width="40">&nbsp;</td>
                                                    <td>
                                                        <p>Hi {{ ucfirst($subscriber->name) }},</p>
                                                    </td>
                                                    <td width="40">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td width="40">&nbsp;</td>
                                                    <td style="font-size:14px;">
                                                        <p style="margin-bottom:20px;">Please see your latest watchlist alerts based on updates to our records.</p>
                                                    </td>
                                                    <td width="40">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="line-height:0;font-size:0" colspan="3" height="35">&nbsp;</td>
                                                </tr>
                                                    <tr>
                                                        <td width="40">&nbsp;</td>
                                                        <td>
                                                            <h4 ><a style="color:#5189c6; margin:0 0 5px; text-decoration: none;" href="{{ url() }}/client/watchlists/manage/{{ $subscriber->watchlist_id }}">{{ strtoupper($subscriber->watchlist->name) }} </a></h4>
                                                            <table style="font-size:13px; line-height:1.2; color:#666666" border="0" cellspacing="3" cellpadding="8" width="600" align="center">
                                                                <thead style="text-align:left;">
                                                                    <tr>
                                                                        <th style="background-color:#f2f2f2; padding:6px 15px">Date/Time</th>
                                                                        <th style="background-color:#f2f2f2; padding:6px 15px">Match On</th>
                                                                        <th style="background-color:#f2f2f2; padding:6px 15px">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($notifications as $notification)
                                                                        <tr>
                                                                            <td style="padding:6px 15px">
                                                                                {{ $notification->created_at->setTimezone($notification->entity->watchlist->creator->timezone)->format('j/m/Y g:i A') }} ({{ $notification->created_at->setTimezone($notification->entity->watchlist->creator->timezone)->diffForHumans() }})
                                                                            </td>
                                                                            <td style="padding:6px 15px">
                                                                                @if($notification->entity->party_name != '')
                                                                                    {{ $notification->entity->party_name .' '}}
                                                                                @endif

                                                                                @if($notification->entity->abn != '')
                                                                                    {{ $notification->entity->abn.' ' }}
                                                                                @endif

                                                                                @if($notification->entity->acn != '')
                                                                                    {{ $notification->entity->acn.' ' }}
                                                                                @endif
                                                                            </td>
                                                                            <td style="padding:6px 15px">
                                                                                <a target="_blank" style="text-decoration: none;" href="{{ url() }}/client/cases/view/{{ $notification->case_id }}" > View Case</a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                        <td width="40">&nbsp;</td>
                                                    </tr>
                                                <tr>
                                                    <td style="line-height:0;font-size:0" colspan="3" height="30">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="line-height:0;font-size:0" colspan="3" height="40">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td width="40">&nbsp;</td>
                                                    <td style="text-align:center;">
                                                        <a href="{{ url() }}/client/watchlists/manage/{{ $subscriber->watchlist_id }}" style="padding:10px 50px; background-color:#4882c3; color:#fff; text-decoration:none;">VIEW YOUR NOTIFICATIONS</a>
                                                    </td>
                                                    <td width="40">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="line-height:0;font-size:0" colspan="3" height="40">&nbsp;</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table style="font-size:13px;line-height:18px;color:#666666" border="0" cellspacing="0" cellpadding="0" width="600" align="center">
                            <tbody>
                                <tr>
                                    <td style="line-height:0;font-size:0" colspan="3" height="20">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="10">&nbsp;</td>
                                    <td style="text-align:center;">
                                         You are receiving this email because you are subscribed to Alares Watchlist Alerts System.
                                    </td>
                                </tr>
                                <tr>
                                    <td style="line-height:0;font-size:0" colspan="3" height="40">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
