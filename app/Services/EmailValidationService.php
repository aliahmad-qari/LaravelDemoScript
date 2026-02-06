<?php

namespace App\Services;

use App\Models\DisposableEmailDomain;
use Illuminate\Support\Facades\Cache;

class EmailValidationService
{
    /**
     * Check if email domain is disposable/fake.
     */
    public function isDisposableEmail(string $email): bool
    {
        $domain = $this->extractDomain($email);

        if (!$domain) {
            return false;
        }

        // Cache the check for 1 hour to reduce DB queries
        return Cache::remember("disposable_email:{$domain}", 3600, function() use ($domain) {
            return DisposableEmailDomain::where('domain', $domain)
                ->where('is_active', true)
                ->exists();
        });
    }

    /**
     * Extract domain from email address.
     */
    private function extractDomain(string $email): ?string
    {
        $parts = explode('@', $email);
        return isset($parts[1]) ? strtolower($parts[1]) : null;
    }

    /**
     * Add disposable domain to blacklist.
     */
    public function addDisposableDomain(string $domain): void
    {
        DisposableEmailDomain::firstOrCreate(
            ['domain' => strtolower($domain)],
            ['is_active' => true]
        );

        Cache::forget("disposable_email:{$domain}");
    }

    /**
     * Remove domain from blacklist.
     */
    public function removeDisposableDomain(string $domain): void
    {
        DisposableEmailDomain::where('domain', strtolower($domain))->delete();
        Cache::forget("disposable_email:{$domain}");
    }

    /**
     * Seed common disposable email domains.
     */
    public function seedCommonDisposableDomains(): void
    {
        $domains = [
            'tempmail.com', 'guerrillamail.com', '10minutemail.com', 'throwaway.email',
            'mailinator.com', 'maildrop.cc', 'temp-mail.org', 'getnada.com',
            'trashmail.com', 'fakeinbox.com', 'yopmail.com', 'sharklasers.com'
        ];

        foreach ($domains as $domain) {
            $this->addDisposableDomain($domain);
        }
    }
}
