<x-app-layout>
    <x-slot name="header">
        <a href="{{route('project-create')}}">
            <x-primary-button>
                Create project
            </x-primary-button>
        </a>
    </x-slot>

    @unless (count($projects) == 0)
    @foreach ($projects as $project)
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 flex items-center justify-between">
                        <div>Title: {{$project->title}}</div>

                        @php
                            $port = App\Http\Controllers\ProjectController::getPort($project);
                        @endphp
                        <div>Port: {{$port}}</div>

                        <form method="POST" action="/Laravel/docker-host/public/project/{{$project->id}}">
                            @csrf
                            @method('DELETE')
                            <x-danger-button>
                                Delete
                            </x-danger-button>
                        </form>
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
                        No projects found
                    </div>
                </div>
            </div>
        </div>
    @endunless
</x-app-layout>
