<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\SecurityService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     * Logic: Create user -> Auto Login -> Initialize Session Security -> Log Network usage.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $ip = $request->ip();

        // Optional: Check IP reputation before even creating the user
        // if ($this->securityService->isLoginRestrictedByGlobalReputation($ip)) { ... }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Auto-login after successful account creation
        Auth::login($user);

        $country = $this->securityService->getCountry($ip);
        
        // CRITICAL: Initialize session variables for IpRestrictionMiddleware
        session([
            'login_ip' => $ip,
            'login_country' => $country,
            'login_user_agent' => $request->userAgent()
        ]); 
        
        // Audit Trail
        $this->securityService->logActivity($user->id, $ip, 'success');
        $this->securityService->registerIp($user, $ip);

        return redirect('/dashboard');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $ip = $request->ip();
        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$ip);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->securityService->logActivity(null, $ip, 'blocked');
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds."
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey);
            $this->securityService->logActivity($user ? $user->id : null, $ip, 'failed');
            return back()->withErrors(['email' => 'Invalid email or password.']);
        }

        if ($this->securityService->isLoginRestricted($user, $ip)) {
            RateLimiter::hit($throttleKey);
            $this->securityService->logActivity($user->id, $ip, 'blocked');
            return back()->withErrors([
                'email' => 'Login blocked: Too many different IP addresses used recently or IP is under cooldown.'
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user);
        
        $country = $this->securityService->getCountry($ip);
        session([
            'login_ip' => $ip,
            'login_country' => $country,
            'login_user_agent' => $request->userAgent()
        ]);

        $this->securityService->logActivity($user->id, $ip, 'success');
        $this->securityService->registerIp($user, $ip);

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}