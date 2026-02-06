# 📧 Email Testing - Quick Start Guide

## Step 1: Setup Mailtrap (2 minutes)

### A. Create Free Account
1. Go to: **https://mailtrap.io/**
2. Click "Sign Up" (it's FREE)
3. Verify your email

### B. Get Your Credentials
1. After login, go to "Email Testing" → "Inboxes"
2. Click on "My Inbox" (or create new one)
3. Click "SMTP Settings" tab
4. You'll see something like:

```
Host: sandbox.smtp.mailtrap.io
Port: 2525
Username: 1a2b3c4d5e6f7g
Password: 1a2b3c4d5e6f7g
```

---

## Step 2: Configure Laravel (1 minute)

### Update `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username_from_mailtrap
MAIL_PASSWORD=your_password_from_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Security System"
```

### Clear config:
```bash
php artisan config:clear
```

---

## Step 3: Start Queue Worker (CRITICAL!)

**Open a NEW terminal** and run:

```bash
php artisan queue:work
```

**⚠️ IMPORTANT**: Keep this terminal open! Emails won't send without it.

You should see:
```
INFO  Processing jobs from the [default] queue.
```

---

## Step 4: Test Security Alert Email (2 minutes)

### Method 1: Login Test
1. **Register a new account** at: http://127.0.0.1:8000/register
2. **Login** at: http://127.0.0.1:8000/login
3. **Check Mailtrap inbox** - you should see email!

### Method 2: Manual Test
Open another terminal and run:

```bash
php artisan tinker
```

Then paste this:

```php
$user = App\Models\User::first();
$alert = App\Models\SecurityAlert::create([
    'user_id' => $user->id,
    'alert_type' => 'new_ip',
    'ip_address' => '192.168.1.100',
    'country' => 'United States',
    'details' => 'Test security alert'
]);
Mail::to($user->email)->queue(new App\Mail\SecurityAlertMail($user, $alert));
```

Press Enter, then type `exit`

---

## Step 5: Check Mailtrap Inbox

1. Go back to **Mailtrap.io**
2. Click on your inbox
3. You should see the email!

### What You'll See:
- **Subject**: "Security Alert: Login from New IP Address"
- **From**: Security System
- **To**: Your email
- **Content**: 
  - Security warning
  - IP address
  - Country
  - Timestamp
  - Instructions

---

## Step 6: Test Abandoned Cart Email (Optional)

### A. Add Items to Cart
1. Login to your account
2. Go to: http://127.0.0.1:8000/products
3. Add some products to cart
4. **Don't checkout** - just leave it

### B. Send Reminder Manually
In terminal, run:

```bash
php artisan cart:send-reminders
```

### C. Check Mailtrap
You should see "You left items in your cart!" email

---

## 🎯 Quick Verification Checklist

After testing, verify:

### ✅ In Queue Worker Terminal:
You should see messages like:
```
[2024-02-06 10:30:45] Processing: Illuminate\Notifications\SendQueuedNotifications
[2024-02-06 10:30:46] Processed:  Illuminate\Notifications\SendQueuedNotifications
```

### ✅ In Mailtrap Inbox:
- [ ] Security alert email received
- [ ] Email has proper formatting
- [ ] All details are correct
- [ ] Links work (if any)

### ✅ In Admin Panel:
1. Go to: http://127.0.0.1:8000/admin/security/alerts
2. Check "Email Sent" column shows: ✓ Yes
3. Check timestamp matches

### ✅ In Database:
```bash
php artisan tinker
App\Models\SecurityAlert::where('email_sent', true)->count();
```
Should return number > 0

---

## 🐛 Troubleshooting

### Problem: No Emails in Mailtrap

**Solution 1: Check Queue Worker**
- Is `php artisan queue:work` running?
- Check the terminal for errors

**Solution 2: Check Mail Config**
```bash
php artisan config:clear
php artisan tinker
config('mail.host')  # Should show: sandbox.smtp.mailtrap.io
```

**Solution 3: Check Failed Jobs**
```bash
php artisan queue:failed
```

If you see failed jobs:
```bash
php artisan queue:retry all
```

**Solution 4: Test Mail Connection**
```bash
php artisan tinker
Mail::raw('Test', function($msg) { $msg->to('test@test.com'); });
```

Check queue worker terminal for errors.

---

### Problem: Queue Worker Stops

**Restart it:**
```bash
php artisan queue:work
```

**For production, use Supervisor** (see IMPLEMENTATION_GUIDE.md)

---

### Problem: Emails Go to Spam

**Don't worry!** Mailtrap catches ALL emails - check:
- Inbox tab
- Spam tab
- All Messages

---

## 📊 What Emails You Should Receive

### 1. Security Alert: New IP
**Trigger**: Login from new IP address
**Subject**: "Security Alert: Login from New IP Address"
**Contains**: IP, country, timestamp

### 2. Security Alert: New Country
**Trigger**: Login from new country
**Subject**: "Security Alert: Login from New Country"
**Contains**: Old country, new country, IP

### 3. Security Alert: Blocked Attempt
**Trigger**: Login blocked by system
**Subject**: "Security Alert: Blocked Login Attempt"
**Contains**: Reason for block, IP, timestamp

### 4. Abandoned Cart Reminder
**Trigger**: Cart inactive for 2+ hours
**Subject**: "You left items in your cart!"
**Contains**: Cart items, prices, checkout link

---

## 🎨 Email Preview

### Security Alert Email Looks Like:

```
┌─────────────────────────────────────┐
│  🔒 Security Alert                  │
├─────────────────────────────────────┤
│                                     │
│  Hi John Doe,                       │
│                                     │
│  ⚠️ Login from new IP address:      │
│     192.168.1.100                   │
│                                     │
│  Details:                           │
│  IP Address: 192.168.1.100          │
│  Country: United States             │
│  Time: 2024-02-06 10:30:45          │
│                                     │
│  Was this you?                      │
│  If not, change your password!      │
│                                     │
└─────────────────────────────────────┘
```

---

## 🚀 Production Setup

When going live, replace Mailtrap with real SMTP:

### Gmail Example:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

### SendGrid Example:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
```

### Mailgun Example:
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your_mailgun_secret
```

---

## ✅ Success Indicators

You'll know everything is working when:

1. ✅ Queue worker shows "Processed" messages
2. ✅ Mailtrap inbox has emails
3. ✅ Admin panel shows "Email Sent: Yes"
4. ✅ Database has `email_sent = 1`
5. ✅ No errors in `storage/logs/laravel.log`

---

## 📞 Quick Help Commands

```bash
# Check if queue is working
php artisan queue:work --once

# See what's in the queue
php artisan queue:work --stop-when-empty

# Clear everything and start fresh
php artisan cache:clear
php artisan config:clear
php artisan queue:restart

# Check logs for errors
Get-Content storage/logs/laravel.log -Tail 20
```

---

## 🎉 You're Done!

Now you can:
- ✅ Receive security alerts via email
- ✅ Send abandoned cart reminders
- ✅ Monitor all emails in Mailtrap
- ✅ Test email templates safely

**Next**: Check `TESTING_GUIDE.md` for complete feature testing!

---

**Need more help?** Check:
- `TESTING_GUIDE.md` - Complete testing instructions
- `IMPLEMENTATION_GUIDE.md` - Detailed feature documentation
- `SETUP_INSTRUCTIONS.md` - Initial setup guide
