@extends('emails.layout')

@section('title', 'Новая заявка на ваш кейс')

@section('content')
    <h2 style="color: #1e40af; margin-top: 0; text-align: center;">Новая заявка на кейс</h2>

    <p style="font-size: 16px; line-height: 1.6;">Здравствуйте!</p>

    <p style="font-size: 16px; line-height: 1.6;">На ваш кейс <strong>"{{ $application->case->title }}"</strong> поступила новая заявка от команды студентов!</p>

    <div class="info-box">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
            Информация о заявке
        </p>
        <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.8;">
            Лидер команды: <strong>{{ $application->leader->name }}</strong><br>
            Email лидера: {{ $application->leader->email }}<br>
            Количество участников: {{ $application->teamMembers->count() + 1 }} из {{ $application->case->required_team_size }}<br>
            Дата подачи: {{ $application->submitted_at->format('d.m.Y H:i') }}
        </p>
    </div>

    @if($application->motivation)
        <div class="info-box" style="background-color: #f8f9fa; border-left-color: #6c757d;">
            <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
                Мотивационное письмо
            </p>
            <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.6; font-style: italic;">
                {{ $application->motivation }}
            </p>
        </div>
    @endif

    <div style="text-align: center; margin: 35px 0;">
        <a href="{{ url('/partner/cases/' . $application->case->id) }}" class="button">
            Просмотреть заявку
        </a>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 13px; color: #6c757d; line-height: 1.5;">
            Пожалуйста, рассмотрите заявку и примите решение об одобрении или отклонении в личном кабинете.
        </p>
    </div>

    <div class="divider"></div>

    <p style="margin-top: 25px; font-size: 15px; color: #333; line-height: 1.6;">
        С уважением,<br>
        <strong style="color: #1e40af;">Команда Кластер</strong>
    </p>
@endsection
