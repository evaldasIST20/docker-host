<?php

namespace App\Jobs;

use App\Http\Controllers\ServiceController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateContainers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $projectId, $name, $body;

    /**
     * Create a new job instance.
     */
    public function __construct($projectId, $name, $body)
    {
        $this->projectId = $projectId;
        $this->name = $name;
        $this->body = $body;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ServiceController::store($this->projectId, $this->name, $this->body);
    }
}
