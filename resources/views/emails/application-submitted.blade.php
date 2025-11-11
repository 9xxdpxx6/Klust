@extends('emails.layout')

@section('title', 'Новая заявка на ваш кейс')

@section('content')
    <h2>Новая заявка на кейс</h2>

    <p>На ваш кейс <strong>"{{ $application->case->title }}"</strong> поступила новая заявка от команды студентов!</p>

    <div class="info-box">
        <strong>Информация о заявке:</strong><br>
        Лидер команды: {{ $application->leader->name }}<br>
        Email лидера: {{ $application->leader->email }}<br>
        Количество участников: {{ $application->teamMembers->count() + 1 }} из {{ $application->case->required_team_size }}<br>
        Дата подачи: {{ $application->submitted_at->format('d.m.Y H:i') }}
    </div>

    @if($application->motivation)
        <p><strong>Мотивационное письмо:</strong></p>
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; font-style: italic;">
            {{ $application->motivation }}
        </div>
    @endif

    <p style="text-align: center;">
        <a href="{{ url('/partner/cases/' . $application->case->id) }}" class="button">
            Просмотреть заявку
        </a>
    </p>

    <p style="font-size: 14px; color: #6c757d;">
        Пожалуйста, рассмотрите заявку и примите решение об одобрении или отклонении в личном кабинете.
    </p>
@endsection
