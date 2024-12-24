<div>
    @if (($disable_filter_by_playlists + false) == false)
        <div class="bg-white border-b-2 border-sc-grey-1 py-2 px-2 flex items-center">
            <span class="px-4">
                <svg class="w-6 h-6 text-sc-orange-9 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M18.8 4H5.2a1 1 0 0 0-.7 1.7l5.3 6 .2.7v4.8c0 .2 0 .4.2.4l3 2.3c.3.2.8 0 .8-.4v-7.1c0-.3 0-.5.2-.7l5.3-6a1 1 0 0 0-.7-1.7Z"/>
                </svg>
            </span>
            @if ($disable_filter_by_playlists === false)
                <select wire:model.live="playlist_filter_by_current" class="block py-2.5 px-0 w-1/2 text-sm text-gray-500 bg-transparent border-0 border-b-2 border-gray-200 appearance-none dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer">
                    <option value="" selected>Filter by Playlist</option>
                    @foreach ($this->getPlaylistsFilterBy() as $pfb)
                        <option value="{{ $pfb->url_id }}">{{ $pfb->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    @endif
    @if ($this->getPlaylist()->shouldDisplay())
    <div class=" max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-4 gap-3">
        @foreach ($this->getVideos() as $video)
            @php
                if(!$video->shouldDisplay())
                    continue;

                $thumbnail_image = 'https://customer-rsp77k1og7ulg0vt.cloudflarestream.com/'.$video->cdn_id.'/thumbnails/thumbnail.jpg';
                // Check if updated_at is 01-11-2024
                if ($video->updated_at->format('d-m-Y') === '2024-11-01') {
                    $thumbnail_image = $video->coverimage_path;
                } elseif ($video->coverimage_path != null) {
                    $thumbnail_image = '/storage/' . $video->coverimage_path;
                }
            @endphp
            <div class="py-8">
                    <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200 flex justify-center">
                        <div>
                        <h3 class="font-bold text-lg text-gray-800 pb-1">{{ $video->name }}</h3>

                        <div style="background-image: url('{{ $thumbnail_image }}'); height: 200px; width: 200px; background-size: cover; background-repeat:   no-repeat; background-position: center center; display: flex; align-items: center; justify-content: center;">
                            <div style="height: 100px; width: 100px;">
                                <a href="/watch/{{ $video->url_id }}">
                                    <img src="/assets/play_symbol.png" alt="" style="opacity: 0.25;">
                                </a>
                            </div>
                        </div>
                    </div>
                    </div>
            </div>
        @endforeach
    </div>

        @php
            $got_session_account = \App\Models\Account::getSessionAccount();
            $account = $got_session_account['account'];
            $is_subscribed_precall = $account->hasActiveSubTo('camp_precall');
        @endphp
        @if (!$is_subscribed_precall)
        <div class="p-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{ $this->getVideos()->links() }}
            </div>
        </div>
        @endif
    @else
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    Access denied: playlist unavailable to this user at this time.
                </div>
            </div>
        </div>
    @endif
</div>
