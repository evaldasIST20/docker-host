<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ServiceController extends Controller
{
    public static function store($projectId, $name, $body) {
        $formFields = [
            'project_id' => $projectId,
            'name' => $name
        ];

        Http::post('http://10.175.25.30:2375/v1.42/services/create', $body);

        Service::create($formFields);
    }

    public static function update($name, $service_version, $body) {
        Http::post('http://10.175.25.30:2375/v1.42/services/'.$name.'/update?version='.$service_version, $body);
    }

    public static function inspect($name) {
        return Http::get('http://10.175.25.30:2375/v1.42/services/'.$name);
    }

    public static function delete(Service $service) {
        Http::delete('http://10.175.25.30:2375/v1.42/services/'.$service->name);

        $service->delete();
    }
}
