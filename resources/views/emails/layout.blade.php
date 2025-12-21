<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Кластер')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 180px;
            height: auto;
            margin-bottom: 15px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .email-body {
            padding: 30px 20px;
        }
        .email-body h2 {
            color: #1e40af;
            margin-top: 0;
        }
        .email-body p {
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 40px;
            background-color: #3b82f6;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #2563eb;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="{{ url('images/logo/logo.png') }}" alt="Кластер" style="max-width: 180px; height: auto; display: block; margin: 0 auto;">
        </div>
        <div class="email-body">
            @yield('content')
        </div>
        <div class="email-footer">
            <p>Это автоматическое письмо, пожалуйста, не отвечайте на него.</p>
            <p>&copy; {{ date('Y') }} Кластер. Все права защищены.</p>
        </div>
    </div>
</body>
</html>
