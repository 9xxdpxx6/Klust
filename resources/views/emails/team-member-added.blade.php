@extends('emails.layout')

@section('title', 'Вы добавлены в команду')

@section('content')
    <h2>Вы добавлены в команду!</h2>

    <p>Здравствуйте, {{ $newMember->name }}!</p>

    <p>Пользователь <strong>{{ $application->leader->name }}</strong> добавил вас в команду для работы над кейсом <strong>"{{ $application->case->title }}"</strong>.</p>

    <div class="info-box">
        <strong>Информация о кейсе:</strong><br>
        Название: {{ $application->case->title }}<br>
        Компания: {{ $application->case->partner->company_name }}<br>
        Лидер команды: {{ $application->leader->name }}<br>
        @if($application->case->deadline)
            Дедлайн: {{ $application->case->deadline->format('d.m.Y') }}<br>
        @endif
        Статус заявки: {{ $application->status === 'pending' ? 'Ожидает одобрения' : 'Одобрена' }}
    </div>

    @if($application->status === 'pending')
        <p><strong>Обратите внимание:</strong> Заявка команды еще не одобрена партнером. Вы получите дополнительное уведомление после рассмотрения заявки.</p>
    @else
        <p><strong>Что дальше?</strong></p>
        <ul>
            <li>Свяжитесь с лидером команды для координации работы</li>
            <li>Ознакомьтесь с деталями кейса в личном кабинете</li>
            <li>Приступайте к выполнению задач согласно плану команды</li>
        </ul>
    @endif

    <p style="text-align: center;">
        <a href="{{ url('/student/team/' . $application->id) }}" class="button">
            Перейти к команде
        </a>
    </p>

    <p style="font-size: 14px; color: #6c757d;">
        Желаем успешной работы в команде!
    </p>
@endsection
