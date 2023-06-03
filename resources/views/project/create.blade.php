<x-app-layout>
<div class="py-4 max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        
    <form method="POST" action="{{ route('project-store') }}">
        @csrf

        <div class="py-4">
            <x-input-label for="title" :value="__('Pavadinimas')" />
            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')"/>
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div class="py-4">
            <x-input-label for="url" :value="__('GitHub nuoroda')" />
            <x-text-input id="url" class="block mt-1 w-full" type="text" name="url" :value="old('url')"/>
            <x-input-error :messages="$errors->get('url')" class="mt-2" />
        </div>

        <div class="py-4">
            <x-input-label for="app_id" :value="__('Technologija')" />
            <select class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-2" name="app_id">
                <option value="">Nepasirinkta</option>
                
                @foreach ($apps as $key => $value)
                <option value="{{ $key }}"> 
                    {{ $value }} 
                </option>
                @endforeach    
            </select>
            <x-input-error :messages="$errors->get('app_id')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ml-4">
                Sukurti
            </x-primary-button>
        </div>
    </form>

</div>
</div>
</x-app-layout>