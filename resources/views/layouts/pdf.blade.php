<!doctype html>
<html>
<head>
    <meta charset="utf-8">

    <meta http-equiv="Content-Type" content="charset=utf-8" />
    <link href='https://fonts.googleapis.com/css?family=Barlow&subset=latin-ext' rel='stylesheet'>

    <style>
        @font-face {
            font-family: 'SpaceGrotesk';
            src: url({{ storage_path('fonts/SpaceGrotesk-Light.ttf') }}) format("truetype");
            font-weight: 300;
            font-style: normal;
        }

        @font-face {
            font-family: 'SpaceGrotesk';
            src: url({{ storage_path('fonts/SpaceGrotesk-Regular.ttf') }}) format("truetype");
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'SpaceGrotesk';
            src: url({{ storage_path('fonts/SpaceGrotesk-SemiBold.ttf') }}) format("truetype");
            font-weight: 500;
            font-style: normal;
        }

        @font-face {
            font-family: 'SpaceGrotesk';
            src: url({{ storage_path('fonts/SpaceGrotesk-Bold.ttf') }}) format("truetype");
            font-weight: 700;
            font-style: normal;
        }

        body {
            font-family: 'SpaceGrotesk', 'sans-serif';
            font-weight: normal;
            padding: 10px;
            color: #273653 !important;
        }

        .light {
            font-weight: 300;
        }

        .semibold {
            font-weight: 500;
        }

        .bold {
            font-weight: 700;
        }

        p {
            font-size: 10px;
            text-align: justify;
            margin: 0 !important;
            padding: 0 !important;
        }

        span {
            font-size: 10px;
        }

        tr, td {
            margin: 0 !important;
            padding: 0 !important;
        }

        @page {
            margin:25px;
            margin-top: 50px;
        }

        header {
            position: fixed;
            left: -25px;
            right: -25px;
            height: 40px;
            text-align: center;
            top: -50px;
        }
    </style>
</head>
<body>

<header style="text-align: right; padding-top: 30px ">
    <img style="float: right; padding-right: 35px;" src="{{ public_path('images/logo_dark.svg') }}" alt="" height="30">
</header>

<div class="content">
    @yield('content')
</div>

</body>
</html>
