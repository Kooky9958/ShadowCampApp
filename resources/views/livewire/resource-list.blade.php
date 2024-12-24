<div>
    @if (($disable_filter_by_resource_lists + false) == false)
        <div class="bg-white border-b-2 border-sc-grey-1 py-2 px-2 flex items-center">
            <span class="px-4">
                <svg class="w-6 h-6 text-sc-orange-9 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M18.8 4H5.2a1 1 0 0 0-.7 1.7l5.3 6 .2.7v4.8c0 .2 0 .4.2.4l3 2.3c.3.2.8 0 .8-.4v-7.1c0-.3 0-.5.2-.7l5.3-6a1 1 0 0 0-.7-1.7Z"/>
                </svg>
            </span>
            @if ($disable_filter_by_resource_lists === false)
                <select wire:model.live="resource_list_filter_by_current" class="block py-2.5 px-0 w-1/2 text-sm text-gray-500 bg-transparent border-0 border-b-2 border-gray-200 appearance-none dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer">
                    <option value="" selected>Filter by Resource List</option>
                    @foreach ($this->getResourceListsFilterBy() as $pfb)
                        <option value="{{ $pfb->url_id }}">{{ $pfb->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    @endif

 
    @if ($this->getResourceList()->shouldDisplay())
        @foreach ($this->getResources() as $resource)

            @php
                if(!$resource->shouldDisplay())
                    continue;
            
                $resource_name = $resource->name;
                $resource_description = $resource->description;

                if ($resource->type == 'video') {
                    $video = \App\Models\Video::where('url_id', $resource->resource_location)->first();

                    $resource_name =  $video->name;
                    $resource_description =  $video->description;

                    if($resource->coverimage_path != null)
                        $thumbnail_image = '/storage/'.$video->coverimage_path;
                    else {
                        $thumbnail_image = 'https://customer-csiaq9r0v1g0069s.cloudflarestream.com/'.$video->cdn_id.'/thumbnails/thumbnail.jpg';
                    }
                }
            @endphp

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                        <h3 class="font-bold text-lg text-gray-800 pb-1">{{ $resource_name }}</h3>

                        @if ($resource->type == 'video')
            
                            <div style="background-image: url('{{ $thumbnail_image }}'); height: 540px; width: 308px; background-size: cover; background-repeat: no-repeat; background-position: center center;">
                                <div style="height: 250px; width: 250px; padding-top: 145px; padding-left: 35px;">
                                    <a href="/watch/{{ $resource->resource_location }}">
                                        <img src="/assets/play_symbol.png" alt="" style="opacity: 0.25;" />
                                    </a>
                                </div>
                            </div>
                        @elseif ($resource->type == 'pdf')
                            <div>
                                <a href="/storage/{{ $resource->resource_location }}" target="_blank">
                                    @if ($resource->coverimage_path != null)
                                        <img src="/storage/{{ $resource->coverimage_path }}" class="w-screen md:max-w-xl" />
                                    @else
                                        <i class="bi bi-filetype-pdf" style="font-size: 8rem; color: #7a4f1f;"></i>
                                    @endif
                                </a>
                            </div>
                        @elseif ($resource->type == 'image')
                            <div>
                                <img src="/storage/{{ $resource->resource_location }}" class="w-screen md:max-w-xl" />
                            </div>
                        @endif
                        <br/>
                        {!! $resource_description !!}
                    </div>
                </div>
            </div>

        @endforeach

        <div class="p-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{ $this->getResources()->links() }}
            </div>
        </div>
    @else
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    Access denied: resource list unavailable to this user at this time.
                </div>
            </div>
        </div>
    @endif
</div>
