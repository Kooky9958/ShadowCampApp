<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            ADMIN, Report: {{ $report_name }}
        </h2>
    </x-slot>

    <div class="py-12">        
        @livewire('admin-c-r-u-d-report', ['report_name' => $report_name])
    </div>

</x-app-layout>