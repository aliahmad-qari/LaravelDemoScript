# 🎯 Admin Quick Reference Card

## 📍 All Admin URLs

### Security Management
```
Main Dashboard:        http://127.0.0.1:8000/admin/security/dashboard
Login Logs:           http://127.0.0.1:8000/admin/logs
Country Restrictions: http://127.0.0.1:8000/admin/security/country-restrictions
Blocked IPs:          http://127.0.0.1:8000/admin/security/blocked-ips
Security Alerts:      http://127.0.0.1:8000/admin/security/alerts
Disposable Emails:    http://127.0.0.1:8000/admin/security/disposable-emails
```

### Product Management
```
Products:             http://127.0.0.1:8000/admin/products
```

### Public Pages
```
Products Catalog:     http://127.0.0.1:8000/products
Shopping Cart:        http://127.0.0.1:8000/cart
Dashboard:            http://127.0.0.1:8000/dashboard
```

---

## ⚡ Quick Actions

### Block a Country
1. Go to: Country Restrictions
2. Enter: Country Code (e.g., `CN`), Country Name (e.g., `China`)
3. Select: `Block`
4. Click: "Add Restriction"

### Block an IP
1. Go to: Blocked IPs
2. Enter: IP Address (e.g., `192.168.1.100`)
3. Enter: Reason (optional)
4. Click: "Block IP"

### Add Disposable Email Domain
1. Go to: Disposable Emails
2. Enter: Domain (e.g., `tempmail.com`)
3. Click: "Add Domain"
4. OR click: "Seed Common Domains" for bulk add

### Create Product
1. Go to: Products
2. Fill in:
   - Name: Product name
   - Description: Product details
   - Price: Dollar amount
   - File Size: e.g., "2.5 MB"
   - Members Only: Check if restricted
3. Click: "Add Product"

---

## 🔍 How to Check Things

### Check if Email Was Sent
1. Go to: Security Alerts
2. Look at "Email Sent" column
3. ✓ Yes = Email sent successfully
4. ✗ No = Email pending or failed

### Check Failed Login Attempts
1. Go to: Login Logs
2. Filter by Status: "failed"
3. See IP addresses and timestamps

### Check Blocked IPs
1. Go to: Blocked IPs
2. See list of all blocked IPs
3. "TEMPORARY" = Auto-unblocks after 30 min
4. "PERMANENT" = Manually blocked

### Check Security Metrics
1. Go to: Security Dashboard
2. See:
   - Failed Logins (last 7 days)
   - Blocked Attempts (last 7 days)
   - Currently Blocked IPs
   - Recent Security Alerts

---

## 🛠️ Common Tasks

### Unblock a User Account
```bash
php artisan tinker
$user = App\Models\User::where('email', 'user@email.com')->first();
$user->update(['failed_login_attempts' => 0, 'locked_until' => null]);
exit
```

### Unblock an IP Address
1. Go to: Blocked IPs
2. Find the IP
3. Click: "Unblock"

### View All Security Alerts
1. Go to: Security Alerts
2. See all alerts with:
   - User name
   - Alert type
   - IP address
   - Country
   - Email status

### Send Abandoned Cart Reminders
```bash
php artisan cart:send-reminders
```

---

## 📊 Understanding Status Badges

### Login Logs
- 🟢 **success** = Login successful
- 🔴 **failed** = Wrong password
- ⚫ **blocked** = Login blocked by system

### Security Alerts
- 🟠 **NEW_COUNTRY** = Login from new country
- 🔵 **NEW_IP** = Login from new IP
- 🟣 **SUSPICIOUS_ACTIVITY** = Unusual behavior
- 🔴 **BLOCKED_ATTEMPT** = Login was blocked

### Blocked IPs
- 🔴 **PERMANENT** = Manually blocked forever
- 🟠 **TEMPORARY** = Auto-blocked, expires in 30 min

### Products
- 🟣 **MEMBERS ONLY** = Only logged-in users can see
- 🟢 **PUBLIC** = Everyone can see

---

## 🎯 Testing Checklist

### Security Features
- [ ] Try 5 wrong passwords → Account locks
- [ ] Login from new IP → Email alert sent
- [ ] Block a country → Can't login from that country
- [ ] Add disposable domain → Can't register with it
- [ ] Block an IP → Can't login from that IP

### Product Features
- [ ] Create public product → Visible to everyone
- [ ] Create members-only product → Hidden from guests
- [ ] Add to cart → Items appear in cart
- [ ] Abandon cart → Reminder email sent (after 2 hours)

### Email Features
- [ ] Login → "New IP" email received
- [ ] Abandon cart → "Cart reminder" email received
- [ ] Check Mailtrap → All emails visible
- [ ] Check admin panel → "Email Sent: Yes"

---

## 🚨 Emergency Actions

### Clear All Blocked IPs
```bash
php artisan tinker
App\Models\BlockedIp::truncate();
exit
```

### Unlock All User Accounts
```bash
php artisan tinker
App\Models\User::query()->update(['failed_login_attempts' => 0, 'locked_until' => null]);
exit
```

### Clear All Security Alerts
```bash
php artisan tinker
App\Models\SecurityAlert::truncate();
exit
```

### Reset Everything
```bash
php artisan migrate:fresh
```
⚠️ WARNING: This deletes ALL data!

---

## 📧 Email Configuration

### Check Current Mail Config
```bash
php artisan tinker
config('mail.mailer')
config('mail.host')
exit
```

### Test Email Sending
```bash
php artisan tinker
Mail::raw('Test email', function($msg) { 
    $msg->to('test@example.com')->subject('Test'); 
});
exit
```

### Check Queue Status
```bash
php artisan queue:work --once
```

---

## 🔧 Useful Commands

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### View Routes
```bash
php artisan route:list
php artisan route:list --path=admin
```

### Check Database
```bash
php artisan db:show
```

### View Logs
```bash
Get-Content storage/logs/laravel.log -Tail 50
```

### Queue Commands
```bash
php artisan queue:work          # Start worker
php artisan queue:failed        # See failed jobs
php artisan queue:retry all     # Retry failed jobs
php artisan queue:restart       # Restart workers
```

---

## 📈 Monitoring Tips

### Daily Checks
1. Check Security Dashboard for unusual activity
2. Review failed login attempts
3. Check blocked IPs list
4. Review security alerts

### Weekly Checks
1. Update disposable email domains
2. Review country restrictions
3. Check abandoned cart conversion
4. Review email delivery rates

### Monthly Checks
1. Analyze security patterns
2. Update security thresholds
3. Review product performance
4. Clean up old logs

---

## 🎓 Pro Tips

### Tip 1: Keep Queue Worker Running
Always have `php artisan queue:work` running for emails to send.

### Tip 2: Use Mailtrap for Testing
Never test with real emails - use Mailtrap.io

### Tip 3: Monitor Failed Logins
High failed login count = possible attack

### Tip 4: Regular Backups
Backup database regularly:
```bash
copy database\database.sqlite database\backup.sqlite
```

### Tip 5: Check Logs Often
```bash
Get-Content storage/logs/laravel.log -Tail 20
```

---

## 📞 Quick Support

### Something Not Working?

1. **Check queue worker** - Is it running?
2. **Clear caches** - `php artisan cache:clear`
3. **Check logs** - `storage/logs/laravel.log`
4. **Verify config** - `.env` file settings
5. **Test database** - `php artisan db:show`

### Common Issues

**Emails not sending?**
→ Start queue worker: `php artisan queue:work`

**Can't login?**
→ Check if IP/account is blocked

**Products not showing?**
→ Check `is_active` field and login status

**Security alerts not appearing?**
→ Check if you're logging in from NEW IP

---

## 🎉 Success Indicators

Everything is working when:
- ✅ Can login/register successfully
- ✅ Security alerts appear in admin panel
- ✅ Emails arrive in Mailtrap
- ✅ Products show correctly (public vs members-only)
- ✅ Cart works properly
- ✅ Failed logins are blocked after 5 attempts
- ✅ No errors in logs

---

## 📚 Documentation Files

- `TESTING_GUIDE.md` - Complete testing instructions
- `EMAIL_TESTING_QUICK_START.md` - Email setup guide
- `IMPLEMENTATION_GUIDE.md` - Detailed feature docs
- `SETUP_INSTRUCTIONS.md` - Initial setup
- `PROJECT_SUMMARY.md` - Project overview

---

**Keep this file handy for quick reference!** 📌
