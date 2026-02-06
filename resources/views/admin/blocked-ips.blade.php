<!DOCTYPE html>
<html>
<head>
    <title>Blocked IPs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: bold; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #f44336; color: white; }
        .btn-success { background: #4CAF50; color: white; }
        .btn-primary { background: #3498db; color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-permanent { background: #f44336; color: white; }
        .badge-temporary { background: #ff9800; color: white; }
        .nav { margin-bottom: 20px; }
        .nav a { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Blocked IP Addresses</h1>
        </div>

        <div class="nav">
            <a href="/admin/security/dashboard">Dashboard</a>
            <a href="/admin/logs">Login Logs</a>
        </div>

        @if(session('success'))
        <div style="background: #4CAF50; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
        @endif

        <div class="card">
            <h2>Block IP Permanently</h2>
            <form method="POST" action="/admin/security/blocked-ips/block">
                @csrf
                <div class="form-group">
                    <label>IP Address</label>
                    <input type="text" name="ip_address" required placeholder="192.168.1.1">
                </div>
                <div class="form-group">
                    <label>Reason (Optional)</label>
                    <input type="text" name="reason" placeholder="Suspicious activity">
                </div>
                <button type="submit" class="btn btn-danger">Block IP</button>
            </form>
        </div>

        <div class="card">
            <h2>Currently Blocked IPs</h2>
            <table>
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>Failed Attempts</th>
                        <th>Type</th>
                        <th>Blocked Until</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blockedIps as $ip)
                    <tr>
                        <td>{{ $ip->ip_address }}</td>
                        <td>{{ $ip->failed_attempts }}</td>
                        <td>
                            <span class="badge badge-{{ $ip->is_permanent ? 'permanent' : 'temporary' }}">
                                {{ $ip->is_permanent ? 'PERMANENT' : 'TEMPORARY' }}
                            </span>
                        </td>
                        <td>{{ $ip->is_permanent ? 'Permanent' : ($ip->blocked_until ? $ip->blocked_until->format('Y-m-d H:i:s') : 'N/A') }}</td>
                        <td>{{ $ip->reason ?? 'N/A' }}</td>
                        <td>
                            <form method="POST" action="/admin/security/blocked-ips/{{ $ip->id }}/unblock" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-success" onclick="return confirm('Unblock this IP?')">Unblock</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">No blocked IPs.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $blockedIps->links() }}
        </div>
    </div>
</body>
</html>
