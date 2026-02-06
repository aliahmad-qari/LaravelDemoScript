# Marketplace Security PoC: IP-Based Restrictions

This project implements a robust security layer for a Laravel marketplace to prevent account sharing or unauthorized multi-device access.

## Core Logic: `SecurityService`
The heart of the system lies in `app/Services/SecurityService.php`. It follows these rules:
1. **Detection:** Checks the `login_ips` table for the specific User ID.
2. **Filtering:** Only counts unique IP addresses recorded within the last **24 hours**.
3. **Threshold:** If the user attempts to login from a 4th unique IP, the login is blocked *before* authentication is finalized.
4. **Audit:** Records the result in `login_logs` with a 'blocked' status.

## Database Schema
- **users**: Standard credentials.
- **login_ips**: Stores `user_id`, `ip_address`, `user_agent`, `country`, and timestamp.
- **login_logs**: Stores `user_id` (nullable for guest attempts), `ip_address`, `status` (success/failed/blocked), and timestamp.

## Installation Guide
1. **Copy Files:** Transfer the `app`, `database`, `resources`, and `routes` folders into your Laravel root.
2. **Migrate:** 
   ```bash
   php artisan migrate
   ```
3. **Register Service:** Laravel handles auto-discovery, so `SecurityService` will be ready for injection in `AuthController`.
4. **Middleware (Optional):** Add `IpRestrictionMiddleware` to your `app/Http/Kernel.php` if session persistence per IP is required.

## Test Cases
- **Case 1 (Valid):** Login with User A from 3 different IPs. All 3 sessions allowed.
- **Case 2 (Block):** Login with User A from a 4th IP. Login rejected with error message.
- **Case 3 (Expire):** Wait 24 hours. The old IPs "expire" from the count, allowing new IPs to be used.
- **Case 4 (Failed):** Use correct email but wrong password. Logged as `failed`.

## Admin View
Access `/admin/logs` to see the complete audit trail, identifying suspicious activity and blocked accounts in real-time.
