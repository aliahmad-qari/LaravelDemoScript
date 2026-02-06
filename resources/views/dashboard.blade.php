<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Security POC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">SecurityPOC</a>
            <div class="navbar-nav ms-auto">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h2>Welcome, {{ $user->name }}!</h2>
                        <p class="text-muted">You have successfully bypassed the IP restriction checks.</p>
                        <hr>
                        <h5>Account Details</h5>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Member Since:</strong> {{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="list-group shadow-sm">
                    <a href="/admin/logs" class="list-group-item list-group-item-action bg-primary text-white font-bold">
                        View System Logs (Admin Only)
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
