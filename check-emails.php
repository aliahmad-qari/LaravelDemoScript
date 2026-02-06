<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SecurityAlert;

echo "📧 Email Status Report\n";
echo "=====================\n\n";

$alerts = SecurityAlert::orderBy('created_at', 'desc')->limit(10)->get();

echo "Recent Security Alerts:\n\n";

foreach ($alerts as $alert) {
    $status = $alert->email_sent ? '✅ SENT' : '❌ PENDING';
    $time = $alert->email_sent_at ? $alert->email_sent_at->format('Y-m-d H:i:s') : 'N/A';
    
    echo "ID: {$alert->id}\n";
    echo "Type: {$alert->alert_type}\n";
    echo "Email Status: {$status}\n";
    echo "Sent At: {$time}\n";
    echo "Details: {$alert->details}\n";
    echo "---\n\n";
}

$sentCount = SecurityAlert::where('email_sent', true)->count();
$pendingCount = SecurityAlert::where('email_sent', false)->count();

echo "\n📊 Summary:\n";
echo "Total Sent: {$sentCount}\n";
echo "Total Pending: {$pendingCount}\n\n";

echo "🌐 Check your Mailtrap inbox at: https://mailtrap.io/\n";
echo "🔗 Or visit admin panel: http://127.0.0.1:8000/admin/security/alerts\n";
