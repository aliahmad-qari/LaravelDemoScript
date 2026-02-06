<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\SecurityAlert;
use App\Mail\SecurityAlertMail;
use Illuminate\Support\Facades\Mail;

echo "📧 Sending Test Security Alert\n";
echo "===============================\n\n";

// Get the user
$user = User::where('email', 'ali.islamic.meh@gmail.com')->first();

if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

echo "✅ User: {$user->name} ({$user->email})\n\n";

// Create a test security alert
echo "Creating security alert...\n";

$alert = SecurityAlert::create([
    'user_id' => $user->id,
    'alert_type' => 'new_ip',
    'ip_address' => '203.0.113.100', // Different IP for testing
    'country' => 'United States',
    'details' => 'Login detected from new IP address: 203.0.113.100 (United States)',
    'email_sent' => false
]);

echo "✅ Alert created (ID: {$alert->id})\n\n";

// Queue the email
echo "Queueing email to: {$user->email}\n";

try {
    Mail::to($user->email)->queue(new SecurityAlertMail($user, $alert));
    
    $alert->update([
        'email_sent' => true,
        'email_sent_at' => now()
    ]);
    
    echo "✅ Email queued successfully!\n\n";
    
    echo "⚡ Next Steps:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Process the email:\n";
    echo "   php artisan queue:work --once\n\n";
    echo "2. Check Mailtrap inbox:\n";
    echo "   https://mailtrap.io/\n";
    echo "   Look for email to: {$user->email}\n\n";
    echo "3. Check admin panel:\n";
    echo "   http://127.0.0.1:8000/admin/security/alerts\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
