<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SecurityService;

class IpRestrictionMiddleware
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Handle an incoming request.
     * 
     * Security Logic:
     * 1. Validate User-Agent (prevent browser-level session hijacking).
     * 2. Strict Geolocation Binding: Compare current Geo with login state.
     * 3. Session Invalidation: Terminate access if significant shift is detected.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $sessionIp = $request->session()->get('login_ip');
            $sessionGeo = $request->session()->get('login_country');
            $sessionUA = $request->session()->get('login_user_agent');
            
            $currentIp = $request->ip();
            $currentUA = $request->userAgent();

            $shouldInvalidate = false;
            $reason = "";

            // 1. Browser/User-Agent Check (Significant Difference)
            if ($sessionUA && $sessionUA !== $currentUA) {
                $shouldInvalidate = true;
                $reason = "Security Alert: Browser or device change detected. Access restricted.";
            }

            // 2. Significant IP & Geolocation Check
            if (!$shouldInvalidate && $sessionIp && $sessionIp !== $currentIp) {
                // Fetch current country to evaluate if the IP change is "significant"
                $currentGeo = $this->securityService->getCountry($currentIp);
                
                // If country shifted, it's a critical security event
                if ($sessionGeo && $sessionGeo !== $currentGeo) {
                    $shouldInvalidate = true;
                    $reason = "Security Alert: Geographic shift detected from {$sessionGeo} to {$currentGeo}. Session terminated.";
                } else {
                    // Same country but different IP: log and allow (standard mobile rotation)
                    // Optionally, you can still invalidate here for higher sensitivity
                    // $shouldInvalidate = true; 
                    // $reason = "Security Alert: Your session is locked to IP {$sessionIp}.";
                }
            }

            if ($shouldInvalidate) {
                $userId = Auth::id();
                $this->securityService->logActivity($userId, $currentIp, 'blocked');

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => $reason]);
            }

            // Persistence fallback for initial session set if missed
            if (!$request->session()->has('login_ip')) {
                $request->session()->put([
                    'login_ip' => $currentIp,
                    'login_country' => $this->securityService->getCountry($currentIp),
                    'login_user_agent' => $currentUA
                ]);
            }
        }

        return $next($request);
    }
}