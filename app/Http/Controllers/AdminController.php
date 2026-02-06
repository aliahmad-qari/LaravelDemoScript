<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\CountryRestriction;
use App\Models\BlockedIp;
use App\Models\SecurityAlert;
use App\Models\DisposableEmailDomain;
use App\Models\Product;
use App\Services\EmailValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $emailValidationService;

    public function __construct(EmailValidationService $emailValidationService)
    {
        $this->emailValidationService = $emailValidationService;
    }

    /**
     * Display the security logs with enriched information from the login_ips table.
     */
    public function logs()
    {
        // We use subqueries to fetch the Country and User Agent from the login_ips table 
        // based on matching User ID and IP Address. This avoids row duplication 
        // that a standard Left Join might cause if multiple sessions exist for the same IP.
        $logs = LoginLog::select('login_logs.*')
            ->addSelect([
                'country' => DB::table('login_ips')
                    ->select('country')
                    ->whereColumn('user_id', 'login_logs.user_id')
                    ->whereColumn('ip_address', 'login_logs.ip_address')
                    ->latest()
                    ->limit(1),
                'user_agent' => DB::table('login_ips')
                    ->select('user_agent')
                    ->whereColumn('user_id', 'login_logs.user_id')
                    ->whereColumn('ip_address', 'login_logs.ip_address')
                    ->latest()
                    ->limit(1),
            ])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.logs', compact('logs'));
    }

    /**
     * Security dashboard with all security metrics.
     */
    public function securityDashboard()
    {
        $failedLogins = LoginLog::where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $blockedAttempts = LoginLog::where('status', 'blocked')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $blockedIps = BlockedIp::where(function($query) {
            $query->where('is_permanent', true)
                  ->orWhere('blocked_until', '>', now());
        })->count();

        $recentAlerts = SecurityAlert::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.security-dashboard', compact(
            'failedLogins',
            'blockedAttempts',
            'blockedIps',
            'recentAlerts'
        ));
    }

    /**
     * Country restrictions management.
     */
    public function countryRestrictions()
    {
        $restrictions = CountryRestriction::orderBy('country_name')->paginate(20);
        return view('admin.country-restrictions', compact('restrictions'));
    }

    public function storeCountryRestriction(Request $request)
    {
        $request->validate([
            'country_code' => 'required|string|size:2',
            'country_name' => 'required|string|max:255',
            'action' => 'required|in:allow,block'
        ]);

        CountryRestriction::create($request->all());

        return redirect()->back()->with('success', 'Country restriction added successfully.');
    }

    public function updateCountryRestriction(Request $request, CountryRestriction $restriction)
    {
        $request->validate([
            'action' => 'required|in:allow,block',
            'is_active' => 'boolean'
        ]);

        $restriction->update($request->all());

        return redirect()->back()->with('success', 'Country restriction updated successfully.');
    }

    public function deleteCountryRestriction(CountryRestriction $restriction)
    {
        $restriction->delete();
        return redirect()->back()->with('success', 'Country restriction deleted successfully.');
    }

    /**
     * Blocked IPs management.
     */
    public function blockedIps()
    {
        $blockedIps = BlockedIp::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.blocked-ips', compact('blockedIps'));
    }

    public function unblockIp(BlockedIp $blockedIp)
    {
        $blockedIp->delete();
        return redirect()->back()->with('success', 'IP address unblocked successfully.');
    }

    public function blockIpPermanently(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string|max:255'
        ]);

        BlockedIp::updateOrCreate(
            ['ip_address' => $request->ip_address],
            [
                'is_permanent' => true,
                'reason' => $request->reason ?? 'Manually blocked by admin'
            ]
        );

        return redirect()->back()->with('success', 'IP address blocked permanently.');
    }

    /**
     * Security alerts management.
     */
    public function securityAlerts()
    {
        $alerts = SecurityAlert::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.security-alerts', compact('alerts'));
    }

    /**
     * Disposable email domains management.
     */
    public function disposableEmails()
    {
        $domains = DisposableEmailDomain::orderBy('domain')->paginate(50);
        return view('admin.disposable-emails', compact('domains'));
    }

    public function storeDisposableEmail(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255|unique:disposable_email_domains,domain'
        ]);

        $this->emailValidationService->addDisposableDomain($request->domain);

        return redirect()->back()->with('success', 'Disposable email domain added successfully.');
    }

    public function deleteDisposableEmail(DisposableEmailDomain $domain)
    {
        $this->emailValidationService->removeDisposableDomain($domain->domain);
        return redirect()->back()->with('success', 'Disposable email domain removed successfully.');
    }

    public function seedDisposableEmails()
    {
        $this->emailValidationService->seedCommonDisposableDomains();
        return redirect()->back()->with('success', 'Common disposable email domains seeded successfully.');
    }

    /**
     * Products management.
     */
    public function products()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.products', compact('products'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'file_size' => 'nullable|string|max:50',
            'members_only' => 'boolean'
        ]);

        Product::create($request->all());

        return redirect()->back()->with('success', 'Product created successfully.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'file_size' => 'nullable|string|max:50',
            'members_only' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $product->update($request->all());

        return redirect()->back()->with('success', 'Product updated successfully.');
    }

    public function deleteProduct(Product $product)
    {
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }
}
