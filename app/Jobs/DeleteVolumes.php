<?php

namespace App\Jobs;

use App\Http\Controllers\VolumeController;
use App\Models\Volume;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteVolumes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $volume;

    /**
     * Create a new job instance.
     */
    public function __construct(Volume $volume)
    {
        $this->volume = $volume;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        VolumeController::delete($this->volume);
    }
}
