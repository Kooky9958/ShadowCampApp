<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            WATCH: {{ $live_stream->name }}
        </h2>
    </x-slot>
    @if($live_stream->shouldDisplay() && $live_stream->isLive())
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-[600px]">

                @if($live_stream->cdn_vendor == 'cloudflare')
                    <div style="position: relative; padding-top: 56.25%;"><iframe src="https://customer-csiaq9r0v1g0069s.cloudflarestream.com/{{ $live_stream->cdn_id }}/iframe" style="border: none; position: absolute; top: 0; left: 0; height: 100%; width: 100%;" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" allowfullscreen="true"></iframe></div>

                @elseif($live_stream->cdn_vendor == 'cdn77')
                    {!! $live_stream->cdn_player_raw_html !!}
                
                @else
                    <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                        ERROR: Urecognised stream type
                    </div>
                
                @endif
                

            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    {!! $live_stream->description !!}
                </div>
            </div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    {!! $live_stream->show_notes !!}
                </div>
            </div>
        </div>
    @elseif(!$live_stream->isLive() && $live_stream->shouldDisplay())
    <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    The video stream is not currently LIVE. Please check back later.
                </div>
            </div>
        </div>
    @else
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    ACCESS DENIED: video unavailable to this user at this time.
                </div>
            </div>
        </div>
    @endif
</x-app-layout>