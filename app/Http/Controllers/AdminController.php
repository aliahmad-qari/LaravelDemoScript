<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
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
}
