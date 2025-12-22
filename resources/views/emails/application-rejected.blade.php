@extends('emails.layout')

@section('title', 'Статус вашей заявки')

@section('content')
    <h2 style="color: #1e40af; margin-top: 0; text-align: center;">Статус вашей заявки</h2>

    <p style="font-size: 16px; line-height: 1.6;">Здравствуйте!</p>

    <p style="font-size: 16px; line-height: 1.6;">К сожалению, ваша заявка на кейс <strong>"{{ $application->case->title }}"</strong> от компании <strong>{{ $application->case->partner->company_name }}</strong> не была одобрена.</p>

    @if($comment)
        <div class="info-box" style="background-color: #fef2f2; border-left-color: #ef4444;">
            <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
                Комментарий от партнера
            </p>
            <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.6;">
                {{ $comment }}
            </p>
        </div>
    @endif

    <div class="info-box" style="background-color: #fffbeb; border-left-color: #f59e0b;">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
            Не расстраивайтесь! Это отличная возможность:
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #333; line-height: 1.8;">
            <li>Подать заявку на другие доступные кейсы</li>
            <li>Усилить свою команду и попробовать снова</li>
            <li>Развить недостающие навыки через симуляторы</li>
            <li>Получить обратную связь и улучшить свой профиль</li>
        </ul>
    </div>

    <div style="text-align: center; margin: 35px 0;">
        <a href="{{ url('/student/cases') }}" class="button">
            Посмотреть другие кейсы
        </a>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 13px; color: #6c757d; line-height: 1.5;">
            Если кнопка не работает, скопируйте и вставьте следующую ссылку в адресную строку браузера:
        </p>
        <p style="margin: 10px 0 0 0; word-break: break-all; font-size: 12px; color: #3b82f6; font-family: monospace; background-color: #f8f9fa; padding: 10px; border-radius: 4px;">
            {{ url('/student/cases') }}
        </p>
    </div>

    <div class="divider"></div>

    <p style="margin-top: 25px; font-size: 15px; color: #333; line-height: 1.6;">
        Продолжайте развиваться и пробовать! Каждый опыт делает вас сильнее.
    </p>

    <p style="margin-top: 15px; font-size: 15px; color: #333; line-height: 1.6;">
        С уважением,<br>
        <strong style="color: #1e40af;">Команда Кластер</strong>
    </p>
@endsection
