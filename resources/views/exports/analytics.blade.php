<table>
    <thead>
        <tr>
            <th>Period</th>
            <th>Total Cases</th>
            <th>Active Cases</th>
            <th>Completed Cases</th>
            <th>Total Applications</th>
            <th>Accepted Applications</th>
            <th>Rejected Applications</th>
            <th>Conversion Rate</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $analytics['period'] ?? 'All Time' }}</td>
            <td>{{ $analytics['total_cases'] ?? 0 }}</td>
            <td>{{ $analytics['active_cases'] ?? 0 }}</td>
            <td>{{ $analytics['completed_cases'] ?? 0 }}</td>
            <td>{{ $analytics['total_applications'] ?? 0 }}</td>
            <td>{{ $analytics['accepted_applications'] ?? 0 }}</td>
            <td>{{ $analytics['rejected_applications'] ?? 0 }}</td>
            <td>{{ $analytics['conversion_rate'] ?? 0 }}%</td>
        </tr>
    </tbody>
</table>

<h3>Top Cases</h3>
<table>
    <thead>
        <tr>
            <th>Case Title</th>
            <th>Applications Count</th>
            <th>Teams Count</th>
            <th>Conversion Rate</th>
        </tr>
    </thead>
    <tbody>
        @foreach($analytics['top_cases'] ?? [] as $topCase)
        <tr>
            <td>{{ $topCase['title'] }}</td>
            <td>{{ $topCase['applications_count'] }}</td>
            <td>{{ $topCase['teams_count'] }}</td>
            <td>{{ $topCase['conversion_rate'] }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>