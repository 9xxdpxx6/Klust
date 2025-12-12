<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Описание</th>
            <th>Партнер</th>
            <th>Статус</th>
            <th>Размер команды</th>
            <th>Срок выполнения</th>
            <th>Дата создания</th>
            <th>Требуемые навыки</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cases as $case)
        <tr>
            <td>{{ $case->id }}</td>
            <td>{{ $case->title }}</td>
            <td>{{ strip_tags($case->description) }}</td>
            <td>{{ $case->partner->company_name ?? 'N/A' }}</td>
            <td>{{ __('case_statuses.' . $case->status, [], 'ru') }}</td>
            <td>{{ $case->required_team_size }}</td>
            <td>{{ $case->deadline->format('d.m.Y') }}</td>
            <td>{{ $case->created_at->format('d.m.Y H:i') }}</td>
            <td>{{ $case->skills->pluck('name')->join(', ') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>