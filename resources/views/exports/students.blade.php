<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Specialty</th>
            <th>Course</th>
            <th>Group</th>
            <th>Created At</th>
            <th>Total Points</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        <tr>
            <td>{{ $student->id }}</td>
            <td>{{ $student->name }}</td>
            <td>{{ $student->email }}</td>
            <td>{{ $student->studentProfile->specialty ?? 'N/A' }}</td>
            <td>{{ $student->studentProfile->course ?? 'N/A' }}</td>
            <td>{{ $student->studentProfile->group ?? 'N/A' }}</td>
            <td>{{ $student->created_at->format('d.m.Y H:i') }}</td>
            <td>{{ $student->studentProfile->total_points ?? 0 }}</td>
        </tr>
        @endforeach
    </tbody>
</table>