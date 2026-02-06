<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Audit - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .ua-tooltip { 
            font-size: 0.7rem; 
            color: #6c757d; 
            max-width: 140px; 
            display: inline-block; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            white-space: nowrap; 
            cursor: help;
            border-bottom: 1px dotted #ccc;
        }
        .ua-tooltip:hover { 
            white-space: normal; 
            word-break: break-all;
            max-width: 350px;
            position: absolute;
            background: #2d3436;
            color: #dfe6e9;
            border: 1px solid #636e72;
            padding: 10px;
            border-radius: 6px;
            box-shadow: 0 10px 15px rgba(0,0,0,0.2);
            z-index: 1050;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 font-monospace">SYSTEM AUDIT TRAIL</h5>
                <a href="/dashboard" class="btn btn-sm btn-outline-light">Exit Admin</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Time</th>
                            <th>Identity</th>
                            <th>Network Context</th>
                            <th>Location</th>
                            <th>Result</th>
                            <th class="pe-4 text-center">Platform</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 font-monospace small">
                                {{ $log->created_at->format('H:i:s') }}<br>
                                <span class="text-muted text-[10px]">{{ $log->created_at->format('Y-m-d') }}</span>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="fw-bold">{{ $log->user->name }}</div>
                                    <div class="text-muted small">{{ $log->user->email }}</div>
                                @else
                                    <span class="text-muted italic">Guest Attempt</span>
                                @endif
                            </td>
                            <td>
                                <code class="bg-light text-primary px-2 py-1 rounded">{{ $log->ip_address }}</code>
                            </td>
                            <td>
                                @if($log->country)
                                    <span class="text-dark small"><i class="bi bi-globe"></i> {{ $log->country }}</span>
                                @else
                                    <span class="text-muted small">Geo unavailable</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $class = $log->status === 'success' ? 'success' : ($log->status === 'blocked' ? 'danger' : 'warning');
                                @endphp
                                <span class="badge bg-{{ $class }}-subtle text-{{ $class }} border border-{{ $class }}-subtle text-uppercase px-3">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td class="pe-4 text-center">
                                <span class="ua-tooltip" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent ?? 'Unknown Agent' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted italic">No activity logs recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white text-center py-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</body>
</html>