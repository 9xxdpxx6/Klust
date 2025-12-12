<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Название кейса</th>
            <th>Лидер команды</th>
            <th>Размер команды</th>
            <th>Статус</th>
            <th>Дата подачи</th>
            <th>Причина отклонения</th>
            <th>Партнер</th>
        </tr>
    </thead>
    <tbody>
        @foreach($applications as $application)
        <tr>
            <td>{{ $application->id }}</td>
            <td>{{ $application->case->title }}</td>
            <td>{{ $application->leader->name }}</td>
            <td>{{ $application->team_size }}</td>
            <td>{{ $application->status->label }}</td>
            <td>{{ $application->submitted_at->format('d.m.Y H:i') }}</td>
            <td>{{ $application->rejection_reason ?? '' }}</td>
            <td>{{ $application->case->partner->company_name ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>