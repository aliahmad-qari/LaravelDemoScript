<?php

namespace App\Mail;

use App\Models\User;
use App\Models\SecurityAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SecurityAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $alert;

    public function __construct(User $user, SecurityAlert $alert)
    {
        $this->user = $user;
        $this->alert = $alert;
    }

    public function build()
    {
        $subject = $this->getSubject();

        return $this->subject($subject)
                    ->view('emails.security-alert')
                    ->with([
                        'userName' => $this->user->name,
                        'alertType' => $this->alert->alert_type,
                        'ipAddress' => $this->alert->ip_address,
                        'country' => $this->alert->country,
                        'details' => $this->alert->details,
                        'timestamp' => $this->alert->created_at->format('Y-m-d H:i:s')
                    ]);
    }

    private function getSubject(): string
    {
        return match($this->alert->alert_type) {
            'new_country' => 'Security Alert: Login from New Country',
            'new_ip' => 'Security Alert: Login from New IP Address',
            'suspicious_activity' => 'Security Alert: Suspicious Activity Detected',
            'blocked_attempt' => 'Security Alert: Blocked Login Attempt',
            default => 'Security Alert: Account Activity'
        };
    }
}
