@extends('emails.layout')

@section('title', 'Статус вашей заявки')

@section('content')
    <h2>Статус вашей заявки</h2>

    <p>К сожалению, ваша заявка на кейс <strong>"{{ $application->case->title }}"</strong> от компании <strong>{{ $application->case->partner->company_name }}</strong> не была одобрена.</p>

    @if($comment)
        <div class="info-box">
            <strong>Комментарий от партнера:</strong><br>
            {{ $comment }}
        </div>
    @endif

    <p><strong>Не расстраивайтесь!</strong> Это отличная возможность:</p>
    <ul>
        <li>Подать заявку на другие доступные кейсы</li>
        <li>Усилить свою команду и попробовать снова</li>
        <li>Развить недостающие навыки через симуляторы</li>
        <li>Получить обратную связь и улучшить свой профиль</li>
    </ul>

    <p style="text-align: center;">
        <a href="{{ url('/student/cases') }}" class="button">
            Посмотреть другие кейсы
        </a>
    </p>

    <p style="font-size: 14px; color: #6c757d;">
        Продолжайте развиваться и пробовать! Каждый опыт делает вас сильнее.
    </p>
@endsection
