<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>People Attach Friend</title>
</head>
<body>
    <table border="0" width="430" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:0 auto 0 auto" >
                <tbody>
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            <p style="margin:10px 0 10px 0;color:#565a5c;font-size:18px">Hi, {{ $data['sender_user_name'] }}</p>
                            <p style="margin:10px 0 10px 0;color:#565a5c;font-size:18px">You Got a New Friend Request.....</p>
                            <p style="margin:10px 0 10px 0;color:#565a5c;font-size:18px">  {{$data['receiver_user_name']}} Recieve your Friend Reqeust.</p>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td height="10" style="line-height:10px" colspan="1">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
</body>
</html>
