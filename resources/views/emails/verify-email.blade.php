@extends('emails.layout')

@section('title', 'Подтверждение email адреса')

@section('content')
    <h2 style="color: #1e40af; margin-top: 0; text-align: center;">Подтверждение email адреса</h2>

    <p style="font-size: 16px; line-height: 1.6;">Здравствуйте!</p>

    <p style="font-size: 16px; line-height: 1.6;">Пожалуйста, нажмите на кнопку ниже, чтобы подтвердить ваш email адрес и завершить регистрацию на платформе <strong>Кластер</strong>.</p>

    <div style="text-align: center; margin: 35px 0;">
        <a href="{{ $url }}" class="button">
            Подтвердить email
        </a>
    </div>

    <div class="info-box">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af; font-weight: 600;">
            Если кнопка не работает
        </p>
        <p style="margin: 0; font-size: 13px; color: #6c757d; line-height: 1.5;">
            Скопируйте и вставьте следующую ссылку в адресную строку браузера:
        </p>
        <p style="margin: 10px 0 0 0; word-break: break-all; font-size: 12px; color: #3b82f6; font-family: monospace; background-color: #f8f9fa; padding: 10px; border-radius: 4px;">
            {{ $url }}
        </p>
    </div>

    <div class="divider"></div>

    <p style="font-size: 14px; color: #6c757d; margin-bottom: 0; line-height: 1.6;">
        Если вы не создавали аккаунт на платформе Кластер, просто проигнорируйте это письмо.
    </p>

    <p style="margin-top: 25px; font-size: 15px; color: #333; line-height: 1.6;">
        С уважением,<br>
        <strong style="color: #1e40af;">Команда Кластер</strong>
    </p>
@endsection

