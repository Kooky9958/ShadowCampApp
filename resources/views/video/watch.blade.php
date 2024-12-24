<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            WATCH: {{ $video->name }}
        </h2>
    </x-slot>
    @if ($video->shouldDisplay())
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-[600px]">

                <iframe src="https://customer-csiaq9r0v1g0069s.cloudflarestream.com/{{ $video->cdn_id }}/iframe?poster=https%3A%2F%2Fcustomer-csiaq9r0v1g0069s.cloudflarestream.com%2F{{ $video->cdn_id }}%2Fthumbnails%2Fthumbnail.jpg%3Ftime%3D%26height%3D600" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" allowfullscreen="true" class="h-full w-full"></iframe>

            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    {!! $video->description !!}
                </div>
            </div>
        </div>
    @else
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    Access denied: video unavailable to this user at this time.
                </div>
            </div>
        </div>
    @endif
</x-app-layout>