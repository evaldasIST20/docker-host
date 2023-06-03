<?php

namespace App\Http\Controllers;

use App\Models\app;
use App\Models\project;
use Illuminate\Http\Request;
use App\Http\Controllers\NetworkController;
use App\Jobs\CreateContainers;
use App\Jobs\CreateImages;
use App\Jobs\DeleteImages;
use App\Jobs\DeleteProject;
use App\Jobs\DeleteVolumes;
use App\Jobs\UpdateContainer;
use App\Models\Container;
use App\Models\Service;

class ProjectController extends Controller
{
    public function index() {
        return view('dashboard', [
            'projects' => auth()->user()->projects()->get()
        ]);
    }

    public function show(project $project) {
        return view('project.show', [
            'project' => $project
        ]);
    }

    public function create() {
        $apps = app::pluck('title', 'id');

        return view('project.create', compact('apps'));
    }

    public function update(Request $request, project $project) {
        $formFields = $request->validate([
            'url' => ['required', 'url'],
        ],[
            'url.required' => 'Prašome įvesti nuorodą',
            'url.url' => 'Prašome įvesti tinkamą nuorodą'
        ]);
        $formFields['image_version'] = $project->image_version+1;

        $project->update($formFields);
        $projectNaming = str_replace(' ', '', $project->title).'_id-'.$project->id;
        $networkName = $projectNaming.'_network';

        if($project->app_id == 3){ //Laravel
            $laravelVolName = $projectNaming.'_laravel_vol';
            $imageName = strtolower($projectNaming.'_laravel_image');

            $createImage = new CreateImages($project->id, $imageName, $project->image_version.'.0', 'Laravel', $project->url);
            dispatch($createImage)->delay(now()->addSeconds(1));

            $laravelContName = $projectNaming.'_laravel_cont';

            $laravelBody = [
                'Name' => $laravelContName,
                'TaskTemplate' => [
                    'ContainerSpec' => [
                        'Image' => $imageName.':'.$project->image_version.'.0',
                        'Mounts' => [
                            [
                                'Target' => '/app',
                                'Source' => $laravelVolName,
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
                        ['TargetPort' => 8000]
                    ]
                ]
            ];

            $serviceVersion = ServiceController::inspect($laravelContName)->json()['Version']['Index'];
            
            $updateLaravelCont = new UpdateContainer($laravelContName, $serviceVersion, $laravelBody);
            dispatch($updateLaravelCont)->delay(now()->addSeconds(80));
        }

        return redirect('/');
    }

    public function store(Request $request) {

        if($request->app_id == 3){
            $formFields = $request->validate([
                'title' => ['required', 'regex:/^[a-zA-Z\s]+$/'],
                'url' => ['required', 'url'],
                'app_id' => 'required'
            ],[
                'title.required' => 'Prašome įvesti pavadinimą',
                'title.regex' => 'Pavadinime gali būti tik raidės',
                'url.required' => 'Prašome įvesti nuorodą',
                'url.url' => 'Prašome įvesti tinkamą nuorodą',
                'app_id.required' => 'Prašome pasirinkti technologiją'
            ]);
            $formFields['image_version'] = 1;
        }else{
            $formFields = $request->validate([
                'title' => ['required', 'regex:/^[a-zA-Z\s]+$/'],
                'app_id' => 'required'
            ],[
                'title.required' => 'Prašome įvesti pavadinimą',
                'title.regex' => 'Pavadinime gali būti tik raidės',
                'app_id.required' => 'Prašome pasirinkti technologiją'
            ]);
        }

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

            $createMysqlCont = new CreateContainers($project->id, $mysqlContName, $mysqlBody);
            dispatch($createMysqlCont)->delay(now()->addSeconds(1));

            $createWordpressCont = new CreateContainers($project->id, $wordpressContName, $wordpressBody);
            dispatch($createWordpressCont)->delay(now()->addSeconds(1));
        
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

            $createPostgresCont = new CreateContainers($project->id, $postgresContName, $postgresBody);
            dispatch($createPostgresCont)->delay(now()->addSeconds(1));

            $createOdooCont = new CreateContainers($project->id, $odooContName, $odooBody);
            dispatch($createOdooCont)->delay(now()->addSeconds(1));

        }else if($project->app_id == 3) { //Laravel
        
            $mariadbVolName = $projectNaming.'_mariadb_vol';
            $laravelVolName = $projectNaming.'_laravel_vol';

            VolumeController::store($project->id, $mariadbVolName);
            VolumeController::store($project->id, $laravelVolName);

            $imageName = strtolower($projectNaming.'_laravel_image');

            $createImage = new CreateImages($project->id, $imageName, $project->image_version.'.0', 'Laravel', $project->url);
            dispatch($createImage)->delay(now()->addSeconds(1));

            $mariadbContName = $projectNaming.'_mariadb_cont';
            $laravelContName = $projectNaming.'_laravel_cont';

            $mariadbBody = [
                'Name' => $mariadbContName,
                'TaskTemplate' => [
                    'ContainerSpec' => [
                        'Image' => 'mariadb:10.6.13',
                        'Env' => [
                            'MYSQL_DATABASE=project_db',
                            'MYSQL_USER=db_user',
                            'MYSQL_PASSWORD=2s4O3%zW1Jx2',
                            'MYSQL_RANDOM_ROOT_PASSWORD=yes'
                        ],
                        'Mounts' => [
                            [
                                'Target' => '/var/lib/mysql',
                                'Source' => $mariadbVolName,
                                'Type' => 'volume',
                                'ReadOnly' => false
                            ]
                        ]
                    ]
                ],
                'Networks' => [
                    [
                        'Aliases' => ['database'],
                        'Target' => $networkName
                    ]
                ]
            ];

            $laravelBody = [
                'Name' => $laravelContName,
                'TaskTemplate' => [
                    'ContainerSpec' => [
                        'Image' => $imageName.':'.$project->image_version.'.0',
                        'Mounts' => [
                            [
                                'Target' => '/app',
                                'Source' => $laravelVolName,
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
                        ['TargetPort' => 8000]
                    ]
                ]
            ];

            $createMadiadbCont = new CreateContainers($project->id, $mariadbContName, $mariadbBody);
            dispatch($createMadiadbCont)->delay(now()->addSeconds(80));

            $createLaravelCont = new CreateContainers($project->id, $laravelContName, $laravelBody);
            dispatch($createLaravelCont)->delay(now()->addSeconds(80));
        }

        return redirect('/');
    }

    public static function getPort($project) {
        foreach($project->services()->get() as $service) {
            if(str_contains($service->name, 'wordpress_cont') || str_contains($service->name, 'odoo_cont') || str_contains($service->name, 'laravel_cont')) {
                $response = ServiceController::inspect($service->name)->json();
                
                if(array_key_exists('Ports', $response['Endpoint']))
                    return $response['Endpoint']['Ports'][0]['PublishedPort'];
                else
                    return "null";
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

        $images = $project->images()->get();
        $imageCount = count($images);
        foreach($images as $image){
            $deleteImage = new DeleteImages($image, $imageCount.'.0');
            dispatch($deleteImage)->delay(now()->addSeconds(65));
            $imageCount--;
        }
        
        $deleteProject = new DeleteProject($project);
        dispatch($deleteProject)->delay(now()->addSeconds(70));
        
        return redirect('/');
    }
}
