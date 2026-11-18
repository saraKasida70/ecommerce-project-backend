<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class TestRabbitMQJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public string $recipient,
        public int $orderId
    ) {
        $this->onConnection('rabbitmq');
    }

    public function handle(): void
    {
        Mail::raw(
            "Your order #{$this->orderId} has been created successfully.",
            function ($message) {
                $message
                    ->to($this->recipient)
                    ->subject("Order #{$this->orderId} Confirmation");
            }
        );
    }
}
