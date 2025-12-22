@extends('emails.layout')

@section('title', 'Добро пожаловать в Кластер - Партнерская панель')

@section('content')
    <h2 style="color: #1e40af; margin-top: 0; text-align: center;">Добро пожаловать в Кластер, {{ $user->name }}!</h2>

    <p style="font-size: 16px; line-height: 1.6;">Здравствуйте!</p>

    <p style="font-size: 16px; line-height: 1.6;">Благодарим вас за регистрацию на платформе <strong>Кластер</strong> в качестве партнера!</p>

    <div class="info-box" style="background-color: #f0fdf4; border-left-color: #22c55e;">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
            Как партнер платформы, вы можете:
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #333; line-height: 1.8;">
            <li>Создавать кейсы для студентов</li>
            <li>Управлять заявками от команд студентов</li>
            <li>Взаимодействовать с командами в процессе работы</li>
            <li>Просматривать аналитику по вашим кейсам</li>
            <li>Находить талантливых студентов для вашей компании</li>
        </ul>
    </div>

    <div class="info-box">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
            Ваши данные
        </p>
        <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.8;">
            Email: {{ $user->email }}<br>
            Компания: {{ $user->partner->company_name ?? 'Не указана' }}
        </p>
    </div>

    <div style="text-align: center; margin: 35px 0;">
        <a href="{{ url('/partner/dashboard') }}" class="button">
            Перейти в партнерскую панель
        </a>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 13px; color: #6c757d; line-height: 1.5;">
            Если кнопка не работает, скопируйте и вставьте следующую ссылку в адресную строку браузера:
        </p>
        <p style="margin: 10px 0 0 0; word-break: break-all; font-size: 12px; color: #3b82f6; font-family: monospace; background-color: #f8f9fa; padding: 10px; border-radius: 4px;">
            {{ url('/partner/dashboard') }}
        </p>
    </div>

    <div class="divider"></div>

    <p style="font-size: 14px; color: #6c757d; margin-bottom: 0; line-height: 1.6;">
        Если у вас есть вопросы по работе с платформой, свяжитесь с администрацией Кластер.
    </p>

    <p style="margin-top: 25px; font-size: 15px; color: #333; line-height: 1.6;">
        С уважением,<br>
        <strong style="color: #1e40af;">Команда Кластер</strong>
    </p>
@endsection
