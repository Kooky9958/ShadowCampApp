<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            {{ $resource_list->name }}
        </h2>
    </x-slot>

    @livewire('resource-list', get_defined_vars())

</x-app-layout>