<!DOCTYPE html>
<html>
<head>
    <title>Security Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; }
        .stat-card .number { font-size: 36px; font-weight: bold; color: #2c3e50; }
        .alerts { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .alert-item { padding: 15px; border-bottom: 1px solid #eee; }
        .alert-item:last-child { border-bottom: none; }
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-danger { background: #f44336; color: white; }
        .badge-warning { background: #ff9800; color: white; }
        .badge-info { background: #2196F3; color: white; }
        .nav { margin-bottom: 20px; }
        .nav a { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
        .nav a:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Security Dashboard</h1>
        </div>

        <div class="nav">
            <a href="/admin/logs">Login Logs</a>
            <a href="/admin/security/country-restrictions">Country Restrictions</a>
            <a href="/admin/security/blocked-ips">Blocked IPs</a>
            <a href="/admin/security/alerts">Security Alerts</a>
            <a href="/admin/security/disposable-emails">Disposable Emails</a>
            <a href="/admin/products">Products</a>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Failed Logins (7 Days)</h3>
                <div class="number">{{ $failedLogins }}</div>
            </div>
            <div class="stat-card">
                <h3>Blocked Attempts (7 Days)</h3>
                <div class="number">{{ $blockedAttempts }}</div>
            </div>
            <div class="stat-card">
                <h3>Currently Blocked IPs</h3>
                <div class="number">{{ $blockedIps }}</div>
            </div>
        </div>

        <div class="alerts">
            <h2>Recent Security Alerts</h2>
            @forelse($recentAlerts as $alert)
            <div class="alert-item">
                <span class="badge badge-{{ $alert->alert_type === 'blocked_attempt' ? 'danger' : ($alert->alert_type === 'new_country' ? 'warning' : 'info') }}">
                    {{ strtoupper(str_replace('_', ' ', $alert->alert_type)) }}
                </span>
                <strong>{{ $alert->user->name ?? 'Unknown' }}</strong> - {{ $alert->details }}
                <br>
                <small>IP: {{ $alert->ip_address }} | {{ $alert->created_at->diffForHumans() }}</small>
            </div>
            @empty
            <p>No recent security alerts.</p>
            @endforelse
        </div>
    </div>
</body>
</html>
