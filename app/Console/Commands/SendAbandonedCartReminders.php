<?php

namespace App\Console\Commands;

use App\Services\CartService;
use Illuminate\Console\Command;

class SendAbandonedCartReminders extends Command
{
    protected $signature = 'cart:send-reminders';
    protected $description = 'Send abandoned cart reminder emails';

    protected $cartService;

    public function __construct(CartService $cartService)
    {
        parent::__construct();
        $this->cartService = $cartService;
    }

    public function handle()
    {
        $this->info('Marking abandoned carts...');
        $marked = $this->cartService->markAbandonedCarts();
        $this->info("Marked {$marked} carts as abandoned.");

        $this->info('Sending abandoned cart reminders...');
        $sent = $this->cartService->sendAbandonedCartReminders();
        $this->info("Sent {$sent} reminder emails.");

        return 0;
    }
}
