<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\SecurityAlert;

echo "👥 Registered Users:\n";
echo "===================\n\n";

$users = User::all();

foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Created: {$user->created_at}\n";
    
    // Check security alerts for this user
    $alerts = SecurityAlert::where('user_id', $user->id)->get();
    echo "Security Alerts: {$alerts->count()}\n";
    
    if ($alerts->count() > 0) {
        foreach ($alerts as $alert) {
            $status = $alert->email_sent ? '✅ SENT' : '❌ PENDING';
            echo "  - {$alert->alert_type}: {$status}\n";
        }
    }
    
    echo "---\n\n";
}

echo "\n📧 All Security Alerts:\n";
echo "======================\n\n";

$allAlerts = SecurityAlert::with('user')->orderBy('created_at', 'desc')->get();

foreach ($allAlerts as $alert) {
    $userName = $alert->user ? $alert->user->name : 'Unknown';
    $userEmail = $alert->user ? $alert->user->email : 'Unknown';
    $status = $alert->email_sent ? '✅ SENT' : '❌ PENDING';
    
    echo "Alert ID: {$alert->id}\n";
    echo "User: {$userName} ({$userEmail})\n";
    echo "Type: {$alert->alert_type}\n";
    echo "Status: {$status}\n";
    echo "Created: {$alert->created_at}\n";
    echo "---\n\n";
}
