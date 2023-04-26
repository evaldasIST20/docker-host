<?php

namespace App\Http\Controllers;

use App\Models\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContainerController extends Controller
{
    public static function store($projectId, $name, $body) {
        $formFields = [
            'project_id' => $projectId,
            'name' => $name
        ];

        Http::post('http://192.168.0.192:2375/v1.42/containers/create?name='.$name, $body);

        Container::create($formFields);
    }

    public static function start($name) {
        Http::post('http://192.168.0.192:2375/v1.42/containers/'.$name.'/start');
    }

    public static function inspect($name) {
        return Http::get('http://192.168.0.192:2375/v1.42/containers/'.$name.'/json');
    }

    public static function delete(Container $container) {
        Http::delete('http://192.168.0.192:2375/v1.42/containers/'.$container->name.'?force=true');

        $container->delete();
    }
}
