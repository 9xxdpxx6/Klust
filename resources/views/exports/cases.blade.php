<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Partner</th>
            <th>Status</th>
            <th>Team Size</th>
            <th>Deadline</th>
            <th>Created At</th>
            <th>Required Skills</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cases as $case)
        <tr>
            <td>{{ $case->id }}</td>
            <td>{{ $case->title }}</td>
            <td>{{ strip_tags($case->description) }}</td>
            <td>{{ $case->partner->company_name ?? 'N/A' }}</td>
            <td>{{ $case->status }}</td>
            <td>{{ $case->required_team_size }}</td>
            <td>{{ $case->deadline->format('d.m.Y') }}</td>
            <td>{{ $case->created_at->format('d.m.Y H:i') }}</td>
            <td>{{ $case->required_skills->pluck('name')->join(', ') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>