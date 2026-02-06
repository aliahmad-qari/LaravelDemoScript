<!DOCTYPE html>
<html>
<head>
    <title>Country Restrictions</title>
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
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-block { background: #f44336; color: white; }
        .badge-allow { background: #4CAF50; color: white; }
        .nav { margin-bottom: 20px; }
        .nav a { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Country Restrictions Management</h1>
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
            <h2>Add Country Restriction</h2>
            <form method="POST" action="/admin/security/country-restrictions">
                @csrf
                <div class="form-group">
                    <label>Country Code (2 letters)</label>
                    <input type="text" name="country_code" maxlength="2" required placeholder="US">
                </div>
                <div class="form-group">
                    <label>Country Name</label>
                    <input type="text" name="country_name" required placeholder="United States">
                </div>
                <div class="form-group">
                    <label>Action</label>
                    <select name="action" required>
                        <option value="block">Block</option>
                        <option value="allow">Allow</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Restriction</button>
            </form>
        </div>

        <div class="card">
            <h2>Current Restrictions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Code</th>
                        <th>Action</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($restrictions as $restriction)
                    <tr>
                        <td>{{ $restriction->country_name }}</td>
                        <td>{{ $restriction->country_code }}</td>
                        <td><span class="badge badge-{{ $restriction->action }}">{{ strtoupper($restriction->action) }}</span></td>
                        <td>{{ $restriction->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>
                            <form method="POST" action="/admin/security/country-restrictions/{{ $restriction->id }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">No country restrictions configured.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $restrictions->links() }}
        </div>
    </div>
</body>
</html>
