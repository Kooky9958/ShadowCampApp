<x-app-layout>
    @foreach ($resource_lists as $resource_list)

    @php
        if(!$resource_list->shouldDisplay())
            continue;
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                <h3 class="font-bold text-lg text-gray-800 pb-1">{{ $resource_list->name }}</h3>
                <br/>
                {!! $resource_list->description !!}<br/>
                <br/>
                <a href="/pc/resource_list/{{ $resource_list->url_id }}">
                    <x-button>
                        {{ __('VIEW') }}
                    </x-button>
                </a>
            </div>
        </div>
    </div>

    @endforeach

</x-app-layout>