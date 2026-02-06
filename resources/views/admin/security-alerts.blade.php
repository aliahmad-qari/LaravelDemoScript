<!DOCTYPE html>
<html>
<head>
    <title>Security Alerts</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: bold; }
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-new_country { background: #ff9800; color: white; }
        .badge-new_ip { background: #2196F3; color: white; }
        .badge-suspicious_activity { background: #9C27B0; color: white; }
        .badge-blocked_attempt { background: #f44336; color: white; }
        .nav { margin-bottom: 20px; }
        .nav a { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Security Alerts</h1>
        </div>

        <div class="nav">
            <a href="/admin/security/dashboard">Dashboard</a>
            <a href="/admin/logs">Login Logs</a>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Alert Type</th>
                        <th>IP Address</th>
                        <th>Country</th>
                        <th>Details</th>
                        <th>Email Sent</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr>
                        <td>{{ $alert->user->name ?? 'Unknown' }}</td>
                        <td><span class="badge badge-{{ $alert->alert_type }}">{{ strtoupper(str_replace('_', ' ', $alert->alert_type)) }}</span></td>
                        <td>{{ $alert->ip_address }}</td>
                        <td>{{ $alert->country ?? 'N/A' }}</td>
                        <td>{{ $alert->details }}</td>
                        <td>{{ $alert->email_sent ? '✓ Yes' : '✗ No' }}</td>
                        <td>{{ $alert->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">No security alerts.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $alerts->links() }}
        </div>
    </div>
</body>
</html>
