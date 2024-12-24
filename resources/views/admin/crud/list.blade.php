<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            ADMIN: {{ $model_instance->getNamePlural() }}
        </h2>
    </x-slot>

    <div class="py-12">
        @livewire('list-context-action')
        
        @livewire('admin-c-r-u-d-retrieve-modal', ['model_fqcn' => $model_fqcn])
        
        @livewire('admin-c-r-u-d-list', ['model_class_name' => $model_class_name])
    </div>

</x-app-layout>