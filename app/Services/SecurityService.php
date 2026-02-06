<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginLog;
use App\Models\LoginIp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
}