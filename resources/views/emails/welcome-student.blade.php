@extends('emails.layout')

@section('title', 'Добро пожаловать в Кластер')

@section('content')
    <h2>Добро пожаловать, {{ $user->name }}!</h2>

    <p>Рады приветствовать вас на платформе <strong>Кластер</strong> – образовательной платформе для работы над реальными кейсами от компаний-партнеров.</p>

    <p>Теперь вы можете:</p>
    <ul>
        <li>Просматривать доступные кейсы от компаний</li>
        <li>Подавать заявки на участие в проектах</li>
        <li>Формировать команды с другими студентами</li>
        <li>Развивать свои навыки и получать достижения</li>
        <li>Проходить симуляторы для повышения уровня</li>
    </ul>

    <div class="info-box">
        <strong>Ваши данные:</strong><br>
        Email: {{ $user->email }}<br>
        ID КубГТУ: {{ $user->studentProfile->kubgtu_id ?? 'Не указан' }}
    </div>

    <p style="text-align: center;">
        <a href="{{ url('/student/dashboard') }}" class="button">
            Перейти на платформу
        </a>
    </p>

    <div class="divider"></div>

    <p style="font-size: 14px; color: #6c757d;">
        Если у вас возникнут вопросы, обратитесь в службу поддержки или к вашему куратору.
    </p>
@endsection
