# Complete Testing Guide - Laravel Security & Marketplace System

## 🔧 Setup Email Testing (IMPORTANT - Do This First!)

### Option 1: Mailtrap (Recommended - Free & Easy)

1. **Sign up for free at**: https://mailtrap.io/
2. **Get your credentials** from the inbox settings
3. **Update your `.env` file**:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2587
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Security System"
```

4. **Clear config cache**:
```bash
php artisan config:clear
```

5. **Start queue worker** (IMPORTANT - emails won't send without this):
```bash
php artisan queue:work
```

Keep this terminal open while testing!

### Option 2: Log Driver (Quick Testing)

If you just want to see emails in logs:

```env
MAIL_MAILER=log
```

Emails will be saved in `storage/logs/laravel.log`

---

## 📋 Complete Testing Checklist

### ✅ 1. Test Security Alert Emails

#### Test A: New IP Alert
1. **Login** with your account from your current location
2. **Check Mailtrap inbox** - you should see "Security Alert: Login from New IP Address"
3. **Verify email contains**:
   - Your IP address
   - Country/location
   - Timestamp
   - Security warning message

#### Test B: New Country Alert (Advanced)
1. Use a VPN to change your country
2. Login again
3. Check email for "Security Alert: Login from New Country"

#### Test C: Check Security Alerts in Admin Panel
1. Go to: http://127.0.0.1:8000/admin/security/alerts
2. You should see all security alerts logged
3. Check "Email Sent" column - should show ✓ Yes

---

### ✅ 2. Test Brute-Force Protection

#### Test Account Locking
1. **Try to login with WRONG password 5 times**
2. On the 6th attempt, you should see:
   > "Your account is temporarily locked due to multiple failed login attempts. Please try again later."
3. **Check admin logs**: http://127.0.0.1:8000/admin/logs
4. You should see 5 "failed" attempts
5. **Wait 30 minutes** OR manually unlock in database:
   ```bash
   php artisan tinker
   $user = App\Models\User::where('email', 'your@email.com')->first();
   $user->update(['failed_login_attempts' => 0, 'locked_until' => null]);
   ```

---

### ✅ 3. Test IP Blocking

#### Test Temporary IP Block
1. **Try to login with wrong password 5 times** (from same IP)
2. Your IP will be blocked for 30 minutes
3. You should see:
   > "This IP address is temporarily blocked due to multiple failed login attempts."
4. **Check blocked IPs**: http://127.0.0.1:8000/admin/security/blocked-ips
5. Your IP should be listed with "TEMPORARY" badge

#### Test Manual IP Blocking
1. Go to: http://127.0.0.1:8000/admin/security/blocked-ips
2. Enter an IP address (e.g., 192.168.1.100)
3. Add reason: "Testing manual block"
4. Click "Block IP"
5. Try to login from that IP - should be blocked

#### Unblock IP
1. Go to blocked IPs page
2. Click "Unblock" button next to the IP
3. IP should be removed from list

---

### ✅ 4. Test Country Restrictions

#### Block a Country
1. Go to: http://127.0.0.1:8000/admin/security/country-restrictions
2. Add a country:
   - Country Code: `US`
   - Country Name: `United States`
   - Action: `Block`
3. Click "Add Restriction"
4. If you're in the US, try to login - should be blocked with message:
   > "Login from United States is not allowed."

#### Allow a Country
1. Change action to "Allow" for testing
2. Or delete the restriction

---

### ✅ 5. Test Disposable Email Blocking

#### Add Disposable Domain
1. Go to: http://127.0.0.1:8000/admin/security/disposable-emails
2. Click "Seed Common Domains" to add popular disposable email providers
3. Or manually add a domain: `tempmail.com`

#### Test Registration Block
1. Go to: http://127.0.0.1:8000/register
2. Try to register with: `test@tempmail.com`
3. You should see error:
   > "Disposable or temporary email addresses are not allowed. Please use a valid email address."

#### Test with Valid Email
1. Register with a real email (e.g., Gmail)
2. Should work fine

---

### ✅ 6. Test Products & Members-Only Feature

#### Create Products
1. Go to: http://127.0.0.1:8000/admin/products
2. **Create Public Product**:
   - Name: "Free eBook"
   - Description: "Available to everyone"
   - Price: 0.00
   - File Size: "2.5 MB"
   - Members Only: ❌ (unchecked)
   - Click "Add Product"

3. **Create Members-Only Product**:
   - Name: "Premium Course"
   - Description: "Only for logged-in users"
   - Price: 99.99
   - File Size: "500 MB"
   - Members Only: ✅ (checked)
   - Click "Add Product"

#### Test as Guest (Logged Out)
1. **Logout** from your account
2. Go to: http://127.0.0.1:8000/products
3. You should see:
   - ✅ "Free eBook" (public product)
   - ❌ "Premium Course" should NOT be visible
4. Try to access directly: http://127.0.0.1:8000/products/2
5. Should get **404 error** for members-only product

#### Test as Logged-In User
1. **Login** to your account
2. Go to: http://127.0.0.1:8000/products
3. You should see:
   - ✅ "Free eBook"
   - ✅ "Premium Course" (with "MEMBERS ONLY" badge)
4. Both products should be accessible

---

### ✅ 7. Test Shopping Cart

#### Add Items to Cart
1. Login to your account
2. Go to: http://127.0.0.1:8000/products
3. Click "Add to Cart" on any product
4. You should see success message
5. Go to: http://127.0.0.1:8000/cart
6. Your items should be listed

#### Remove Items
1. In cart page, click "Remove" button
2. Item should be removed

---

### ✅ 8. Test Abandoned Cart Emails

#### Setup
1. Make sure queue worker is running:
   ```bash
   php artisan queue:work
   ```

#### Test Abandoned Cart
1. **Add items to cart** but don't checkout
2. **Wait** (or manually trigger):
   ```bash
   php artisan cart:send-reminders
   ```
3. **Check Mailtrap inbox** for "You left items in your cart!" email
4. Email should contain:
   - Cart items list
   - Quantities and prices
   - "Complete Your Purchase" button

#### Check Cart Status
1. Check database or admin panel
2. Cart should be marked as "abandoned"
3. `reminder_sent` should be true

---

### ✅ 9. Test Security Dashboard

1. Go to: http://127.0.0.1:8000/admin/security/dashboard
2. You should see:
   - **Failed Logins (7 Days)**: Count of failed attempts
   - **Blocked Attempts (7 Days)**: Count of blocked logins
   - **Currently Blocked IPs**: Number of blocked IPs
   - **Recent Security Alerts**: List of latest alerts

3. Click on different sections:
   - Login Logs
   - Country Restrictions
   - Blocked IPs
   - Security Alerts
   - Disposable Emails
   - Products

---

## 🧪 Quick Test Scenarios

### Scenario 1: Complete Security Test (15 minutes)
1. ✅ Register new account
2. ✅ Login successfully (check for "new IP" email)
3. ✅ Logout and try 5 wrong passwords (test brute-force)
4. ✅ Check admin logs for failed attempts
5. ✅ Check security alerts page
6. ✅ Check Mailtrap for security emails

### Scenario 2: Product & Cart Test (10 minutes)
1. ✅ Create 2 products (1 public, 1 members-only)
2. ✅ Logout and verify members-only is hidden
3. ✅ Login and verify both products visible
4. ✅ Add items to cart
5. ✅ View cart
6. ✅ Remove items

### Scenario 3: Email Testing (5 minutes)
1. ✅ Login from new IP (check email)
2. ✅ Add items to cart and abandon (check email)
3. ✅ Try 5 wrong passwords (check blocked email)
4. ✅ Verify all emails in Mailtrap

---

## 📧 How to Verify Emails Are Working

### Check Mailtrap Inbox
1. Login to Mailtrap.io
2. Go to your inbox
3. You should see emails like:
   - 🔒 "Security Alert: Login from New IP Address"
   - 🔒 "Security Alert: Login from New Country"
   - 🔒 "Security Alert: Blocked Login Attempt"
   - 🛒 "You left items in your cart!"

### Check Email Details
Each email should have:
- ✅ Proper subject line
- ✅ User's name
- ✅ Relevant details (IP, country, timestamp)
- ✅ Professional formatting
- ✅ Clear call-to-action

### Check Database
```bash
php artisan tinker
App\Models\SecurityAlert::where('email_sent', true)->count();
```
Should show number of emails sent.

---

## 🐛 Troubleshooting

### Emails Not Sending?

**Check 1: Queue Worker Running?**
```bash
# Make sure this is running in a separate terminal
php artisan queue:work
```

**Check 2: Mail Configuration**
```bash
php artisan config:clear
php artisan tinker
config('mail.mailer')  # Should show 'smtp' or 'log'
```

**Check 3: Test Email Manually**
```bash
php artisan tinker
Mail::raw('Test email', function($msg) { 
    $msg->to('your@email.com')->subject('Test'); 
});
```

**Check 4: Check Failed Jobs**
```bash
php artisan queue:failed
```

**Check 5: Check Logs**
```bash
# View last 50 lines of log
Get-Content storage/logs/laravel.log -Tail 50
```

### Can't See Security Alerts?

1. Make sure you're logging in from a NEW IP
2. Check: http://127.0.0.1:8000/admin/security/alerts
3. Check database:
   ```bash
   php artisan tinker
   App\Models\SecurityAlert::all();
   ```

### Products Not Showing?

1. Check product is active: `is_active = 1`
2. Check you're logged in for members-only products
3. Clear cache: `php artisan cache:clear`

---

## 📊 Expected Results Summary

After complete testing, you should have:

### In Mailtrap Inbox:
- ✅ At least 1 "New IP" security alert email
- ✅ At least 1 abandoned cart email (if tested)
- ✅ Professional email formatting

### In Admin Panel:
- ✅ Login logs showing success/failed/blocked attempts
- ✅ Security alerts with "Email Sent: Yes"
- ✅ Blocked IPs (if tested)
- ✅ Country restrictions (if added)
- ✅ Disposable email domains (if seeded)
- ✅ Products created

### In Database:
- ✅ `security_alerts` table has records
- ✅ `login_logs` table has records
- ✅ `blocked_ips` table has records (if tested)
- ✅ `products` table has records
- ✅ `carts` table has records (if tested)

---

## 🎯 Production Checklist

Before going live:

- [ ] Configure real SMTP service (not Mailtrap)
- [ ] Set up Supervisor for queue worker
- [ ] Set up cron for abandoned cart reminders
- [ ] Test all email templates
- [ ] Add real disposable email domains
- [ ] Configure country restrictions
- [ ] Set proper security thresholds in `.env`
- [ ] Enable SSL/HTTPS
- [ ] Test from different IPs/countries
- [ ] Monitor security alerts regularly

---

## 🚀 Quick Commands Reference

```bash
# Start queue worker (REQUIRED for emails)
php artisan queue:work

# Send abandoned cart reminders manually
php artisan cart:send-reminders

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check routes
php artisan route:list

# Check database tables
php artisan db:show

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Check logs
Get-Content storage/logs/laravel.log -Tail 50
```

---

## 📞 Need Help?

If something doesn't work:
1. Check `storage/logs/laravel.log`
2. Make sure queue worker is running
3. Verify mail configuration in `.env`
4. Clear all caches
5. Check database tables exist

---

**Happy Testing! 🎉**

All features are working and ready for production use!
