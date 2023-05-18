<?php

namespace App\Http\Controllers;

use App\Models\app;
use App\Models\project;
use Illuminate\Http\Request;
use App\Http\Controllers\NetworkController;
use App\Jobs\DeleteProject;
use App\Jobs\DeleteVolumes;
use App\Models\Container;
use App\Models\Service;

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

            $mysqlContName = $projectNaming.'_mysql_cont';
            $wordpressContName = $projectNaming.'_wordpress_cont';

            $mysqlBody = [
                'Name' => $mysqlContName,
                'TaskTemplate' => [
                    'ContainerSpec' => [
                        'Image' => 'mysql:8.0.32',
                        'Env' => [
                            'MYSQL_DATABASE=wp_db',
                            'MYSQL_USER=wp_user',
                            'MYSQL_PASSWORD=2s4O3%zW1Jx2',
                            'MYSQL_RANDOM_ROOT_PASSWORD=yes'
                        ],
                        'Mounts' => [
                            [
                                'Target' => '/var/lib/mysql',
                                'Source' => $mysqlVolName,
                                'Type' => 'volume',
                                'ReadOnly' => false
                            ]
                        ]
                    ]
                ],
                'Networks' => [
                    [
                        'Aliases' => ['mysql'],
                        'Target' => $networkName
                    ]
                ]
            ];

            $wordpressBody = [
                'Name' => $wordpressContName,
                'TaskTemplate' => [
                    'ContainerSpec' => [
                        'Image' => 'wordpress:6.2.0',
                        'Env' => [
                            'WORDPRESS_DB_HOST=mysql',
                            'WORDPRESS_DB_NAME=wp_db',
                            'WORDPRESS_DB_USER=wp_user',
                            'WORDPRESS_DB_PASSWORD=2s4O3%zW1Jx2'
                        ],
                        'Mounts' => [
                            [
                                'Target' => '/var/www/html',
                                'Source' => $wordpressVolName,
                                'Type' => 'volume',
                                'ReadOnly' => false
                            ]
                        ]
                    ]
                ],
                'Networks' => [
                    ['Target' => $networkName]
                ],
                "EndpointSpec" => [
                    'Ports' => [
                        ['TargetPort' => 80]
                    ]
                ]
            ];

            ServiceController::store($project->id, $mysqlContName, $mysqlBody);
            ServiceController::store($project->id, $wordpressContName, $wordpressBody);
        
        }else if($project->app_id == 2) { //Odoo
            
            $postgresVolName = $projectNaming.'_postgres_vol';
            $odooWebVolName = $projectNaming.'_odoo_web_vol';
            $odooExtraVolName = $projectNaming.'_odoo_extra_vol';

            VolumeController::store($project->id, $postgresVolName);
            VolumeController::store($project->id, $odooWebVolName);
            VolumeController::store($project->id, $odooExtraVolName);

            $postgresContName = $projectNaming.'_postgres_cont';
            $odooContName = $projectNaming.'_odoo_cont';

            $postgresBody = [
                'Name' => $postgresContName,
                'TaskTemplate' => [
                    'ContainerSpec' => [
                        'Image' => 'postgres:15.3',
                        'Env' => [
                            'POSTGRES_USER=odoo',
                            'POSTGRES_PASSWORD=2s4O3%zW1Jx2',
                            'POSTGRES_DB=postgres'
                        ],
                        'Mounts' => [
                            [
                                'Target' => '/var/lib/postgresql/data',
                                'Source' => $postgresVolName,
                                'Type' => 'volume',
                                'ReadOnly' => false
                            ]
                        ]
                    ]
                ],
                'Networks' => [
                    [
                        'Aliases' => ['postgres'],
                        'Target' => $networkName
                    ]
                ]
            ];

            $odooBody = [
                'Name' => $odooContName,
                'TaskTemplate' => [
                    'ContainerSpec' => [
                        'Image' => 'odoo:16.0',
                        'Env' => [
                            'HOST=postgres',
                            'USER=odoo',
                            'PASSWORD=2s4O3%zW1Jx2',
                        ],
                        'Mounts' => [
                            [
                                'Target' => '/var/lib/odoo',
                                'Source' => $odooWebVolName,
                                'Type' => 'volume',
                                'ReadOnly' => false
                            ],
                            [
                                'Target' => '/mnt/extra-addons',
                                'Source' => $odooExtraVolName,
                                'Type' => 'volume',
                                'ReadOnly' => false
                            ],
                        ]
                    ]
                ],
                'Networks' => [
                    ['Target' => $networkName]
                ],
                "EndpointSpec" => [
                    'Ports' => [
                        ['TargetPort' => 8069]
                    ]
                ]
            ];

            ServiceController::store($project->id, $postgresContName, $postgresBody);
            ServiceController::store($project->id, $odooContName, $odooBody);

        }

        return redirect('/');
    }

    public static function getPort($project) {
        foreach($project->services()->get() as $service) {
            if(str_contains($service->name, 'wordpress') || str_contains($service->name, 'odoo')) {
                return ServiceController::inspect($service->name)->json()['Endpoint']['Ports'][0]['PublishedPort'];
            }
        }
    }

    public function delete(project $project) {
        if($project->user_id != auth()->id())
            abort(403, 'Unauthorized Action');

        foreach($project->services()->get() as $service)
            ServiceController::delete($service);

        foreach($project->networks()->get() as $network)
            NetworkController::delete($network);
        
        foreach($project->volumes()->get() as $volume){
            $deleteVolume = new DeleteVolumes($volume);
            dispatch($deleteVolume)->delay(now()->addSeconds(60));
        }
        
        $deleteProject = new DeleteProject($project);
        dispatch($deleteProject)->delay(now()->addSeconds(70));
        
        return redirect('/');
    }
}
