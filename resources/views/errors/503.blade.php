<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page_title ?? 'Maintenance Mode' }}</title>

    <link href="{{ secure_asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ secure_asset('vendor/font-awesome/css/fontawesome-all.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('vendor/jquery-confirm/jquery-confirm.min.css') }}">
    <script src="{{ secure_asset('vendor/jquery/jquery-3.3.1.js') }}"></script>

    <style>
        body {
            background: #1a1c23;
            font-family: Arial, sans-serif;
        }
        .maintenance-box {
            max-width: 500px;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            background: #262932;
            color: #fff;
            text-align: center;
            box-shadow: 0px 10px 25px rgba(0,0,0,0.3);
        }
        .maintenance-box h1 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        .maintenance-box p {
            font-size: 16px;
            color: #ccc;
            margin: 10px 0;
        }
    </style>
</head>
<body class="content">
    <main role="main">
        <div class="maintenance-box">
            <h1>⚙️ Under Maintenance</h1>
            <p>The service is temporarily unavailable.</p>
            <p>You have been logged out. Please check back later.</p>
        </div>
    </main>
</body>
</html>
