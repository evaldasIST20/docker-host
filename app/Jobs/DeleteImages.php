<?php

namespace App\Jobs;

use App\Http\Controllers\ImageController;
use App\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $image, $tag;

    /**
     * Create a new job instance.
     */
    public function __construct(Image $image, $tag)
    {
        $this->image = $image;
        $this->tag = $tag;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ImageController::delete($this->image, $this->tag);
    }
}
