<?php

namespace App\Http\Controllers;

use App\Models\Volume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VolumeController extends Controller
{
    public static function store($projectId, $name) {
        $formFields = [
            'project_id' => $projectId,
            'name' => $name
        ];

        $body = [
            'Name' => $name,
            'Driver' => 'local'
        ];

        Http::post('http://10.175.25.30:2375/v1.42/volumes/create', $body);

        Volume::create($formFields);
    }

    public static function delete(Volume $volume) {
        Http::delete('http://10.175.25.30:2375/v1.42/volumes/'.$volume->name);

        $volume->delete();
    }
}
