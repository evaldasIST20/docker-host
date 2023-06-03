<?php

namespace App\Jobs;

use App\Http\Controllers\ImageController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $projectId, $name, $tag, $dockerfile_tile, $repo_url;

    /**
     * Create a new job instance.
     */
    public function __construct($projectId, $name, $tag, $dockerfile_tile, $repo_url)
    {
        $this->projectId = $projectId;
        $this->name = $name;
        $this->tag = $tag;
        $this->dockerfile_tile = $dockerfile_tile;
        $this->repo_url = $repo_url;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ImageController::store($this->projectId, $this->name, $this->tag, $this->dockerfile_tile, $this->repo_url);
    }
}
