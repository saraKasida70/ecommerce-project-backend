<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TestFailingRabbitMQJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct()
    {
        $this->onConnection('rabbitmq');
        $this->onQueue('orders_test');
    }

    public function handle(): void
    {
        Log::info('DLQ test attempt', [
            'attempt' => $this->attempts(),
            'job' => self::class,
        ]);

        throw new RuntimeException(
            'Intentional failure for DLQ test.'
        );
    }
}