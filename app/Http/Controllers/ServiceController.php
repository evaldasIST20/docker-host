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

        Http::post('http://192.168.0.192:2375/v1.42/services/create', $body);

        Service::create($formFields);
    }

    public static function inspect($name) {
        return Http::get('http://192.168.0.192:2375/v1.42/services/'.$name);
    }

    public static function delete(Service $service) {
        Http::delete('http://192.168.0.192:2375/v1.42/services/'.$service->name);

        $service->delete();
    }
}
