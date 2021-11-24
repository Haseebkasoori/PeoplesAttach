<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comment on Post</title>
</head>
<body>
    <table border="0" width="430" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:0 auto 0 auto" >
                <tbody>
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            <p style="margin:10px 0 10px 0;color:#565a5c;font-size:18px">Hi {{$details['name']}} </p>
                            <p style="margin:10px 0 10px 0;color:#565a5c;font-size:18px">You got a new Comment on your post {{ substr($details['text'], 10) }}.....</p>
                            <p style="margin:10px 0 10px 0;color:#565a5c;font-size:18px">Click the link below to view comment </p>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td height="10" style="line-height:10px" colspan="1">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <a href="{{$details['link']}}"<strong style="color:#ffff;text-decoration:none;display:block;width: 13em;text-align:center;background:#47a2ea;padding:0.5em;font-size:20px;margin-left: 4em; ">{{ $details['link'] }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
</body>
</html>