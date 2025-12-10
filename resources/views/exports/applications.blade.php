<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Case Title</th>
            <th>Leader Name</th>
            <th>Team Size</th>
            <th>Status</th>
            <th>Submitted At</th>
            <th>Rejection Reason</th>
            <th>Partner</th>
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