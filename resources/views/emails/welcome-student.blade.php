@extends('emails.layout')

@section('title', 'Добро пожаловать в Кластер')

@section('content')
    <h2 style="color: #1e40af; margin-top: 0; text-align: center;">Добро пожаловать, {{ $user->name }}!</h2>

    <p style="font-size: 16px; line-height: 1.6;">Здравствуйте!</p>

    <p style="font-size: 16px; line-height: 1.6;">Рады приветствовать вас на платформе <strong>Кластер</strong> – образовательной платформе для работы над реальными кейсами от компаний-партнеров.</p>

    <div class="info-box" style="background-color: #f0fdf4; border-left-color: #22c55e;">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
            Теперь вы можете:
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #333; line-height: 1.8;">
            <li>Просматривать доступные кейсы от компаний</li>
            <li>Подавать заявки на участие в проектах</li>
            <li>Формировать команды с другими студентами</li>
            <li>Развивать свои навыки и получать достижения</li>
            <li>Проходить симуляторы для повышения уровня</li>
        </ul>
    </div>

    <div class="info-box">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
            Ваши данные
        </p>
        <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.8;">
            Email: {{ $user->email }}<br>
            ID КубГТУ: {{ $user->studentProfile->kubgtu_id ?? 'Не указан' }}
        </p>
    </div>

    <div style="text-align: center; margin: 35px 0;">
        <a href="{{ url('/student/dashboard') }}" class="button">
            Перейти на платформу
        </a>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 13px; color: #6c757d; line-height: 1.5;">
            Если кнопка не работает, скопируйте и вставьте следующую ссылку в адресную строку браузера:
        </p>
        <p style="margin: 10px 0 0 0; word-break: break-all; font-size: 12px; color: #3b82f6; font-family: monospace; background-color: #f8f9fa; padding: 10px; border-radius: 4px;">
            {{ url('/student/dashboard') }}
        </p>
    </div>

    <div class="divider"></div>

    <p style="font-size: 14px; color: #6c757d; margin-bottom: 0; line-height: 1.6;">
        Если у вас возникнут вопросы, обратитесь в службу поддержки или к вашему куратору.
    </p>

    <p style="margin-top: 25px; font-size: 15px; color: #333; line-height: 1.6;">
        С уважением,<br>
        <strong style="color: #1e40af;">Команда Кластер</strong>
    </p>
@endsection
