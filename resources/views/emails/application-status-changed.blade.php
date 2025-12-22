@extends('emails.layout')

@section('title', 'Статус вашей заявки изменен')

@section('content')
    @php
        // Определяем цвета в зависимости от нового статуса
        $statusColors = [
            'accepted' => [
                'primary' => '#22c55e',      // Зеленый
                'light' => '#f0fdf4',         // Светло-зеленый фон
                'border' => '#22c55e',        // Зеленая граница
                'button' => '#22c55e',        // Зеленая кнопка
                'button_hover' => '#16a34a', // Темно-зеленый при наведении
            ],
            'rejected' => [
                'primary' => '#ef4444',       // Красный
                'light' => '#fef2f2',         // Светло-красный фон
                'border' => '#ef4444',        // Красная граница
                'button' => '#ef4444',        // Красная кнопка
                'button_hover' => '#dc2626', // Темно-красный при наведении
            ],
            'pending' => [
                'primary' => '#1e40af',        // Синий
                'light' => '#eff6ff',         // Светло-синий фон
                'border' => '#3b82f6',        // Синяя граница
                'button' => '#3b82f6',       // Синяя кнопка
                'button_hover' => '#2563eb', // Темно-синий при наведении
            ],
        ];
        
        // Определяем статус для цветов (используем переданный statusKey)
        $statusKey = $statusKey ?? ($application->status->name ?? 'pending');
        $colors = $statusColors[$statusKey] ?? $statusColors['pending'];
    @endphp

    <h2 style="color: {{ $colors['primary'] }}; margin-top: 0; text-align: center;">Статус вашей заявки изменен</h2>

    <p style="font-size: 16px; line-height: 1.6;">Здравствуйте!</p>

    <p style="font-size: 16px; line-height: 1.6;">Статус вашей заявки на кейс <strong>"{{ $application->case->title }}"</strong> был изменен.</p>

    <div class="info-box" style="background-color: {{ $colors['light'] }}; border-left-color: {{ $colors['border'] }};">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: {{ $colors['primary'] }}; font-weight: 600;">
            Информация об изменении статуса
        </p>
        <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.8;">
            Предыдущий статус: <strong>{{ $oldStatusName }}</strong><br>
            Новый статус: <strong>{{ $newStatusName }}</strong>
        </p>
    </div>

    @if($comment)
        <div class="info-box" style="background-color: #f8f9fa; border-left-color: #6c757d;">
            <p style="margin: 0 0 10px 0; font-size: 14px; color: {{ $colors['primary'] }}; font-weight: 600;">
                Комментарий
            </p>
            <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.6;">
                {{ $comment }}
            </p>
        </div>
    @endif

    <div style="text-align: center; margin: 35px 0;">
        <a href="{{ $actionUrl }}" style="display: inline-block; padding: 14px 40px; background-color: {{ $colors['button'] }}; color: #ffffff !important; text-decoration: none; border-radius: 6px; margin: 20px 0; font-weight: bold; font-size: 16px; transition: background-color 0.3s;">
            {{ $actionText }}
        </a>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 13px; color: #6c757d; line-height: 1.5;">
            Если кнопка не работает, скопируйте и вставьте следующую ссылку в адресную строку браузера:
        </p>
        <p style="margin: 10px 0 0 0; word-break: break-all; font-size: 12px; color: #3b82f6; font-family: monospace; background-color: #f8f9fa; padding: 10px; border-radius: 4px;">
            {{ $actionUrl }}
        </p>
    </div>

    <div class="divider"></div>

    <p style="margin-top: 25px; font-size: 15px; color: #333; line-height: 1.6;">
        С уважением,<br>
        <strong style="color: {{ $colors['primary'] }};">Команда Кластер</strong>
    </p>
@endsection

