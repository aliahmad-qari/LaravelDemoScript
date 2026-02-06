<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginLog;
use App\Models\LoginIp;
use App\Models\CountryRestriction;
use App\Models\BlockedIp;
use App\Models\SecurityAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\SecurityAlertMail;

class SecurityService
{
    /**
     * Check if the user is allowed to login from the current IP.
     * Implements a 1-hour cooldown for blocked IPs using high-performance Cache.
     */
    public function isLoginRestricted(User $user, string $ip): bool
    {
        // 1. Check for 1-hour Temporary Block (Cache Layer)
        $blockKey = "ip_restricted_cooldown:{$user->id}:{$ip}";
        if (Cache::has($blockKey)) {
            return true;
        }

        // 2. Database Fallback (Check if user was blocked from this IP in last hour)
        $wasRecentlyBlocked = LoginLog::where('user_id', $user->id)
            ->where('ip_address', $ip)
            ->where('status', 'blocked')
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->exists();

        if ($wasRecentlyBlocked) {
            // Re-prime the cache for 1 hour to prevent heavy DB hits
            Cache::put($blockKey, true, 3600);
            return true;
        }

        // 3. Unique IP Count (24h Window)
        // Only count unique IPs that were registered as a "LoginIp" record
        $recentIps = LoginIp::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->pluck('ip_address')
            ->unique();

        // If this IP is already known for today, proceed
        if ($recentIps->contains($ip)) {
            return false;
        }

        // Block if attempting to add a 4th unique IP
        return $recentIps->count() >= 3;
    }

    /**
     * Log activity and implement 1-hour cache block for 'blocked' events.
     */
    public function logActivity(?int $userId, string $ip, string $status): void
    {
        LoginLog::create([
            'user_id' => $userId,
            'ip_address' => $ip,
            'status' => $status,
            'created_at' => now(),
        ]);

        // If blocked, enforce a 1-hour block on this IP via Cache
        if ($status === 'blocked' && $userId) {
            Cache::put("ip_restricted_cooldown:{$userId}:{$ip}", true, 3600);
        }
    }

    /**
     * Register a new IP address usage with geolocation.
     */
    public function registerIp(User $user, string $ip): void
    {
        LoginIp::create([
            'user_id' => $user->id,
            'ip_address' => $ip,
            'user_agent' => Request::userAgent(),
            'country' => $this->getCountry($ip),
            'created_at' => now(),
        ]);
    }

    /**
     * Fetch country using ip-api.com.
     */
    public function getCountry(string $ip): string
    {
        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
            if ($response->successful() && $response->json('status') === 'success') {
                return $response->json('country') ?? 'Unknown Region';
            }
        } catch (\Exception $e) {
            Log::warning("Geolocation lookup failed for IP {$ip}: " . $e->getMessage());
        }
        return 'Local Access';
    }

    /**
     * Check if country is restricted for login.
     */
    public function isCountryRestricted(string $country): bool
    {
        $restriction = CountryRestriction::where('country_name', $country)
            ->where('is_active', true)
            ->first();

        if (!$restriction) {
            return false;
        }

        return $restriction->action === 'block';
    }

    /**
     * Check if IP is temporarily or permanently blocked.
     */
    public function isIpBlocked(string $ip): bool
    {
        $blockedIp = BlockedIp::where('ip_address', $ip)->first();

        if (!$blockedIp) {
            return false;
        }

        return $blockedIp->isBlocked();
    }

    /**
     * Increment failed login attempts for an IP.
     */
    public function recordFailedAttempt(string $ip): void
    {
        $blockedIp = BlockedIp::firstOrCreate(
            ['ip_address' => $ip],
            ['failed_attempts' => 0]
        );

        $blockedIp->increment('failed_attempts');

        $maxAttempts = config('security.max_failed_attempts', 5);
        $blockDuration = config('security.ip_block_duration', 30); // minutes

        if ($blockedIp->failed_attempts >= $maxAttempts) {
            $blockedIp->update([
                'blocked_until' => Carbon::now()->addMinutes($blockDuration),
                'reason' => 'Too many failed login attempts'
            ]);
        }
    }

    /**
     * Clear failed attempts for an IP after successful login.
     */
    public function clearFailedAttempts(string $ip): void
    {
        BlockedIp::where('ip_address', $ip)->delete();
    }

    /**
     * Check if user account is locked due to brute force.
     */
    public function isAccountLocked(User $user): bool
    {
        if (!$user->locked_until) {
            return false;
        }

        if (Carbon::now()->lessThan($user->locked_until)) {
            return true;
        }

        // Unlock if time has passed
        $user->update([
            'locked_until' => null,
            'failed_login_attempts' => 0
        ]);

        return false;
    }

    /**
     * Record failed login attempt for user account.
     */
    public function recordUserFailedAttempt(User $user): void
    {
        $user->increment('failed_login_attempts');

        $maxAttempts = config('security.max_user_failed_attempts', 5);
        $lockDuration = config('security.account_lock_duration', 30); // minutes

        if ($user->failed_login_attempts >= $maxAttempts) {
            $user->update([
                'locked_until' => Carbon::now()->addMinutes($lockDuration)
            ]);
        }
    }

    /**
     * Clear user failed attempts after successful login.
     */
    public function clearUserFailedAttempts(User $user): void
    {
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * Detect and alert on suspicious login activity.
     */
    public function detectSuspiciousActivity(User $user, string $ip, string $country): void
    {
        // Check for new country
        $hasLoggedFromCountry = LoginIp::where('user_id', $user->id)
            ->where('country', $country)
            ->exists();

        if (!$hasLoggedFromCountry && $country !== 'Local Access') {
            $this->createSecurityAlert($user, 'new_country', $ip, $country, "Login from new country: {$country}");
        }

        // Check for new IP
        $hasLoggedFromIp = LoginIp::where('user_id', $user->id)
            ->where('ip_address', $ip)
            ->exists();

        if (!$hasLoggedFromIp) {
            $this->createSecurityAlert($user, 'new_ip', $ip, $country, "Login from new IP address: {$ip}");
        }
    }

    /**
     * Create security alert and send email notification.
     */
    public function createSecurityAlert(User $user, string $type, string $ip, ?string $country, string $details): void
    {
        $alert = SecurityAlert::create([
            'user_id' => $user->id,
            'alert_type' => $type,
            'ip_address' => $ip,
            'country' => $country,
            'details' => $details,
            'email_sent' => false
        ]);

        // Queue email notification
        try {
            Mail::to($user->email)->queue(new SecurityAlertMail($user, $alert));
            
            $alert->update([
                'email_sent' => true,
                'email_sent_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send security alert email: " . $e->getMessage());
        }
    }

    /**
     * Get country code from country name for API calls.
     */
    public function getCountryCode(string $ip): ?string
    {
        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
            if ($response->successful() && $response->json('status') === 'success') {
                return $response->json('countryCode');
            }
        } catch (\Exception $e) {
            Log::warning("Country code lookup failed for IP {$ip}: " . $e->getMessage());
        }
        return null;
    }
}
