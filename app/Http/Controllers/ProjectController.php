<?php

namespace App\Http\Controllers;

use App\Models\app;
use App\Models\project;
use Illuminate\Http\Request;
use App\Http\Controllers\NetworkController;
use App\Models\Container;

class ProjectController extends Controller
{
    public function index() {
        return view('dashboard', [
            'projects' => auth()->user()->projects()->get()
        ]);
    }

    public function create() {
        $apps = app::pluck('title', 'id');

        return view('project.create', compact('apps'));
    }

    public function store(Request $request) {
        $formFields = $request->validate([
            'title' => 'required',
            'app_id' => 'required'
        ]);

        $formFields['user_id'] = auth()->id();

        $project = project::create($formFields);
        $projectNaming = str_replace(' ', '', $project->title).'_id-'.$project->id;
        $networkName = $projectNaming.'_network';

        NetworkController::store($project->id, $networkName);

        if($project->app_id == 1) { //WordPress
            $mysqlVolName = $projectNaming.'_mysql_vol';
            $wordpressVolName = $projectNaming.'_wordpress_vol';

            VolumeController::store($project->id, $mysqlVolName);
            VolumeController::store($project->id, $wordpressVolName);

            $mysqlBody = [
                'Image' => 'mysql:8.0.32',
                'Hostname' => 'mysql',
                'Env' => [
                    'MYSQL_DATABASE=wp_db',
                    'MYSQL_USER=wp_user',
                    'MYSQL_PASSWORD=2s4O3%zW1Jx2',
                    'MYSQL_RANDOM_ROOT_PASSWORD=yes'
                ],
                'NetworkMode' => $networkName,
                'Mounts' => [
                    [
                        'Target' => '/var/lib/mysql',
                        'Source' => $mysqlVolName,
                        'Type' => 'volume',
                        'ReadOnly' => false
                    ]
                ]
            ];

            $wordpressBody = [
                'Image' => 'wordpress:6.2.0',
                'Env' => [
                    'WORDPRESS_DB_HOST=mysql',
                    'WORDPRESS_DB_NAME=wp_db',
                    'WORDPRESS_DB_USER=wp_user',
                    'WORDPRESS_DB_PASSWORD=2s4O3%zW1Jx2'
                ],
                'PortBindings' => [
                    '80/tcp' => [
                        [
                            'HostPort' => ''
                        ]
                    ]
                ],
                'NetworkMode' => $networkName,
                'Mounts' => [
                    [
                        'Target' => '/var/www/html',
                        'Source' => $wordpressVolName,
                        'Type' => 'volume',
                        'ReadOnly' => false
                    ]
                ]
            ];

            $mysqlContName = $projectNaming.'_mysql_cont';
            $wordpressContName = $projectNaming.'_wordpress_cont';

            ContainerController::store($project->id, $mysqlContName, $mysqlBody);
            ContainerController::store($project->id, $wordpressContName, $wordpressBody);

            ContainerController::start($mysqlContName);
            sleep(60);
            ContainerController::start($wordpressContName);
        }

        return redirect('/');
    }

    public static function getPort($project) {
        if($project->app_id == 1) {
            foreach($project->containers()->get() as $container) {
                if(str_contains($container->name, 'wordpress')) {
                    //Gets key=value array first key
                    $key = array_key_first(ContainerController::inspect($container->name)->json()['NetworkSettings']['Ports']);

                    if($key != null)
                        return ContainerController::inspect($container->name)->json()['NetworkSettings']['Ports'][$key][0]['HostPort'];
                    else
                        return 'Container off';
                }
            }
        }
    }

    public function delete(project $project) {
        if($project->user_id != auth()->id())
            abort(403, 'Unauthorized Action');

        foreach($project->containers()->get() as $container)
            ContainerController::delete($container);

        foreach($project->networks()->get() as $network)
            NetworkController::delete($network);
        
        foreach($project->volumes()->get() as $volume)
            VolumeController::delete($volume);
        
        $project->delete();
        
        return redirect('/');
    }
}
