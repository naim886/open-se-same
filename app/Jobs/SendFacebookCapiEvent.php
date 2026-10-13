<?php

namespace App\Jobs;

use App\Services\FacebookCapiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFacebookCapiEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Retry up to 3 times on failure */
    public int $tries = 3;

    /** Wait 30 seconds between retries */
    public int $backoff = 30;

    public function __construct(
        public readonly string $eventName,
        public readonly array  $userData,
        public readonly array  $customData,
        public readonly string $eventId,
        public readonly string $sourceUrl = ''
    ) {}

    public function handle(FacebookCapiService $capiService): void
    {
        $capiService->sendEvent(
            $this->eventName,
            $this->userData,
            $this->customData,
            $this->eventId,
            $this->sourceUrl
        );
    }
}
