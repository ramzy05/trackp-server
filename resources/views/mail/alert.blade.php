<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel Email Alert</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css?family=figtree:400,600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        /* Add your custom styles here */

        /* Example styles */
        body {
            font-family: 'figtree', sans-serif;
            background-color: #FBFBFE;
            color: #3C4247;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 768px;
            margin: 0 auto;
            padding: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 200px;
        }

        .message {
            background-color: #DCF2FF;
            padding: 20px;
            margin-bottom: 20px;
        }

        .message p {
            margin: 0;
            padding: 0;
        }

        .message .username {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message">
            <p>Dear Admin,</p>
            <p>We regret to inform you that the agent <span class="username">{{ $agent->fullname }}</span> is currently outside the designated zone.</p>
            <p>Please take appropriate action to address this issue.</p>
        </div>
        <p>Thank you,</p>
        <p>Your Company</p>
    </div>
</body>
</html>
