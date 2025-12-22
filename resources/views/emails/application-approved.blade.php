@extends('emails.layout')

@section('title', 'Ваша заявка одобрена!')

@section('content')
    <h2 style="color: #1e40af; margin-top: 0; text-align: center;">🎉 Поздравляем! Ваша заявка одобрена!</h2>

    <p style="font-size: 16px; line-height: 1.6;">Здравствуйте!</p>

    <p style="font-size: 16px; line-height: 1.6;">Отличные новости! Ваша заявка на кейс <strong>"{{ $application->case->title }}"</strong> от компании <strong>{{ $application->case->partner->company_name }}</strong> была одобрена!</p>

    <div class="info-box">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
            Информация о кейсе
        </p>
        <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.8;">
            Название: <strong>{{ $application->case->title }}</strong><br>
            Компания: {{ $application->case->partner->company_name }}<br>
            @if($application->case->deadline)
                Дедлайн: {{ $application->case->deadline->format('d.m.Y') }}<br>
            @endif
            Состав команды: {{ $application->teamMembers->count() + 1 }} человек
        </p>
    </div>

    <div class="info-box" style="background-color: #f0fdf4; border-left-color: #22c55e;">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
            Что дальше?
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #333; line-height: 1.8;">
            <li>Перейдите в личный кабинет и ознакомьтесь с деталями кейса</li>
            <li>Свяжитесь со своей командой для планирования работы</li>
            <li>Следите за дедлайнами и выполняйте задачи вовремя</li>
            <li>Взаимодействуйте с представителем компании при необходимости</li>
        </ul>
    </div>

    <div style="text-align: center; margin: 35px 0;">
        <a href="{{ url('/student/team/' . $application->id) }}" class="button">
            Перейти к команде
        </a>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 13px; color: #6c757d; line-height: 1.5;">
            Если кнопка не работает, скопируйте и вставьте следующую ссылку в адресную строку браузера:
        </p>
        <p style="margin: 10px 0 0 0; word-break: break-all; font-size: 12px; color: #3b82f6; font-family: monospace; background-color: #f8f9fa; padding: 10px; border-radius: 4px;">
            {{ url('/student/team/' . $application->id) }}
        </p>
    </div>

    <div class="divider"></div>

    <p style="margin-top: 25px; font-size: 15px; color: #333; line-height: 1.6;">
        С уважением,<br>
        <strong style="color: #1e40af;">Команда Кластер</strong>
    </p>
@endsection
