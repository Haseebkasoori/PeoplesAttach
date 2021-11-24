<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>
<body>
    <h1>We Got request for createing new account!!. Please click the link blow to varify your email</h1>
    <a href="{{$details['link']}}">{{$details['link']}}</a>
</body>
</html>