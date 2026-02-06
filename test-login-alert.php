<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\SecurityService;

echo "🔐 Testing Login Security Alert\n";
echo "================================\n\n";

// Get the user
$user = User::where('email', 'ali.islamic.meh@gmail.com')->first();

if (!$user) {
    echo "❌ User not found with email: ali.islamic.meh@gmail.com\n";
    echo "Please register first at: http://127.0.0.1:8000/register\n";
    exit(1);
}

echo "✅ User found:\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n\n";

// Simulate login security check
$securityService = new SecurityService();
$ip = '127.0.0.1';
$country = $securityService->getCountry($ip);

echo "🌐 Simulating login from:\n";
echo "   IP: {$ip}\n";
echo "   Country: {$country}\n\n";

// Check if this is a new IP
$hasLoggedFromIp = App\Models\LoginIp::where('user_id', $user->id)
    ->where('ip_address', $ip)
    ->exists();

echo "📊 Login History:\n";
echo "   Has logged from this IP before: " . ($hasLoggedFromIp ? 'Yes' : 'No') . "\n\n";

if (!$hasLoggedFromIp) {
    echo "🚨 This is a NEW IP! Security alert will be triggered.\n\n";
    
    // Trigger security alert
    echo "Creating security alert...\n";
    $securityService->detectSuspiciousActivity($user, $ip, $country);
    
    echo "✅ Security alert created!\n\n";
    
    // Check if email was queued
    $alert = App\Models\SecurityAlert::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->first();
    
    if ($alert) {
        echo "📧 Alert Details:\n";
        echo "   Type: {$alert->alert_type}\n";
        echo "   IP: {$alert->ip_address}\n";
        echo "   Country: {$alert->country}\n";
        echo "   Email Queued: " . ($alert->email_sent ? 'Yes' : 'Pending') . "\n\n";
        
        echo "⚡ Next Steps:\n";
        echo "1. Run: php artisan queue:work --once\n";
        echo "2. Check Mailtrap: https://mailtrap.io/\n";
        echo "3. Check admin panel: http://127.0.0.1:8000/admin/security/alerts\n";
    }
} else {
    echo "ℹ️  User has logged from this IP before.\n";
    echo "   No new security alert will be created.\n\n";
    echo "💡 To trigger a new alert:\n";
    echo "   - Login from a different IP address\n";
    echo "   - Or clear login history and try again\n";
}
