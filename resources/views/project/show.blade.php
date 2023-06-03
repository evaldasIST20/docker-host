<x-app-layout>
<div class="py-4 max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        
    <h2>Pavadinimas: {{$project->title}}</h2>

    <form method="POST" action="{{ route('project-update', $project->id) }}">
        @csrf
        @method('PUT')

        <div class="py-4">
            <x-input-label for="url" :value="__('GitHub nuoroda')" />
            <x-text-input id="url" class="block mt-1 w-full" type="text" name="url" :value="old('url')"/>
            <x-input-error :messages="$errors->get('url')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ml-4">
                Atnaujinti
            </x-primary-button>
        </div>
    </form>

</div>
</div>
</x-app-layout>