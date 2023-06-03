<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public static function store($projectId, $name, $tag, $dockerfile_tile, $repo_url) {
        $formFields = [
            'project_id' => $projectId,
            'name' => $name
        ];

        Image::create($formFields);

        $dockerfile = Storage::disk('local')->get('dockerfiles/'.$dockerfile_tile.'.tar.gz');

        Http::withBody(
            $dockerfile, 'application/gzip'
        )->timeout(-1)->post('http://10.175.25.30:2375/v1.42/build?t='.$name.':'.$tag.'&buildargs=%7B"REPO_URL":"'.$repo_url.'"%7D');
    }

    public static function delete(Image $image, $tag) {
        Http::delete('http://10.175.25.30:2375/v1.42/images/'.$image->name.':'.$tag.'?force=true');

        $image->delete();
    }
}
