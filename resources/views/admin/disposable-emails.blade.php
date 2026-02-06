<!DOCTYPE html>
<html>
<head>
    <title>Disposable Email Domains</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: bold; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #3498db; color: white; }
        .btn-danger { background: #f44336; color: white; }
        .btn-success { background: #4CAF50; color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .nav { margin-bottom: 20px; }
        .nav a { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Disposable Email Domains</h1>
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
            <h2>Add Disposable Email Domain</h2>
            <form method="POST" action="/admin/security/disposable-emails">
                @csrf
                <div class="form-group">
                    <label>Domain</label>
                    <input type="text" name="domain" required placeholder="tempmail.com">
                </div>
                <button type="submit" class="btn btn-primary">Add Domain</button>
                <a href="/admin/security/disposable-emails/seed" class="btn btn-success" onclick="return confirm('Seed common disposable email domains?')">Seed Common Domains</a>
            </form>
        </div>

        <div class="card">
            <h2>Blocked Domains ({{ $domains->total() }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Status</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($domains as $domain)
                    <tr>
                        <td>{{ $domain->domain }}</td>
                        <td>{{ $domain->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>{{ $domain->created_at->format('Y-m-d') }}</td>
                        <td>
                            <form method="POST" action="/admin/security/disposable-emails/{{ $domain->id }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Remove this domain?')">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">No disposable email domains configured.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $domains->links() }}
        </div>
    </div>
</body>
</html>
