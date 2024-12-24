<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            {{ $playlist->name }}
        </h2>
    </x-slot>

    @livewire('video-playlist', get_defined_vars())

</x-app-layout>