<?php

namespace App\Http\Controllers;

use App\Models\Network;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NetworkController extends Controller
{
    public static function store($projectId, $name) {
        $formFields = [
            'project_id' => $projectId,
            'name' => $name
        ];

        $body = [
            'Name' => $name,
            'Driver' => 'bridge'
        ];

        Http::post('http://10.175.25.30:2375/v1.42/networks/create', $body);

        Network::create($formFields);
    }

    public static function delete(Network $network) {
        Http::delete('http://10.175.25.30:2375/v1.42/networks/'.$network->name);

        $network->delete();
    }
}
