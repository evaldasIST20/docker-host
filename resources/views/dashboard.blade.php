<x-app-layout>
    <x-slot name="header">
        <a href="{{route('project-create')}}">
            <x-primary-button>
                Naujas projektas
            </x-primary-button>
        </a>
    </x-slot>

    @unless (count($projects) == 0)
    @foreach ($projects as $project)
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 flex items-center justify-between">
                        <div>Pavadinimas: {{$project->title}}</div>

                        @php
                            $port = App\Http\Controllers\ProjectController::getPort($project);
                        @endphp
                        <div>Nuoroda: <a href="http://192.168.0.192:{{$port}}" target="_blank">192.168.0.192:{{$port}}</a></div>

                        <div class="flex items-center">
                            @if ($project->app_id == 3)
                                <a href="{{route('project-show', $project->id)}}">
                                    <x-secondary-button>
                                        Keisti
                                    </x-secondary-button>
                                </a>
                            @endif

                            <form method="POST" action="{{route('project-delete', $project->id)}}">
                                @csrf
                                @method('DELETE')
                                <x-danger-button>
                                    Ištrinti
                                </x-danger-button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @else
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Projektų nėra
                    </div>
                </div>
            </div>
        </div>
    @endunless
</x-app-layout>
