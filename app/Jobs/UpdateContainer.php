<?php

namespace App\Jobs;

use App\Http\Controllers\ServiceController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateContainer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $name, $service_version, $body;

    /**
     * Create a new job instance.
     */
    public function __construct($name, $service_version, $body)
    {
        $this->name = $name;
        $this->service_version = $service_version;
        $this->body = $body;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ServiceController::update($this->name, $this->service_version, $this->body);
    }
}
