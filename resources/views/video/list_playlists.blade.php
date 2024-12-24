<x-app-layout>
    @foreach ($playlists as $playlist)

    @php
        if(!$playlist->shouldDisplay())
            continue;
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                <h3 class="font-bold text-lg text-gray-800 pb-1">{{ $playlist->name }}</h3>
                <br/>
                {!! $playlist->description !!}<br/>
                <br/>
                <a href="/watch/playlist/{{ $playlist->url_id }}">
                    <x-button>
                        {{ __('WATCH') }}
                    </x-button>
                </a>
            </div>
        </div>
    </div>

    @endforeach

</x-app-layout>