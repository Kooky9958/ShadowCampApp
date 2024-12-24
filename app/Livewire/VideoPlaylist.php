<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class VideoPlaylist extends Component
{
    use WithPagination;

    /**
     * Storage location to persisted state
     */
    public ?array $livewire_persist = null;

    /**
     * Current playlist we are filtering by
     */
    public ?string $playlist_filter_by_current = null;

    /**
     * Whether or not to disable filtering by playlists
     */
    public bool $disable_filter_by_playlists = true;

    /**
     * Most recent videos fetched
     * @var \Illuminate\Support\Collection
     */
    private $got_videos;

    /**
     * Get the playlists to filter by
     */
    public function getPlaylistsFilterBy() {
        return \App\Models\VideoPlaylist::whereIn('id', $this->livewire_persist['playlists_filter_by_ids'])->get();
    }

    /**
     * Get the playlist we are displaying
     */
    public function getPlaylist() {
        return \App\Models\VideoPlaylist::getPlaylist($this->livewire_persist['playlist_url_id']);
    }

    /**
     * Get the videos in the playlist
     */
    public function getVideos() {
        // Check have we already got the videos
        if($this->got_videos !== null)
            return $this->got_videos;

        // Compose the query to fetch the videos
        if ($this->playlist_filter_by_current != null) {
            $this->resetPage();
            
            $query = \App\Models\Video::where('playlist', 'like' , '%'.$this->playlist_filter_by_current.'%')
                        ->whereIn('id', $this->livewire_persist['videos_all_ids']);
        }
        else {
            $query = \App\Models\Video::whereIn('id', $this->livewire_persist['videos_all_ids']);
        }

        $got_session_account = \App\Models\Account::getSessionAccount();
        $account = $got_session_account['account'];

        if($account->hasActiveSubTo('camp_precall')) {
            // Execute the query, store the result, and return it
            return $this->got_videos = $query->orderByDesc('date_created')->get();
        }
        else {
            // Execute the query, store the result, and return it
            return ($this->got_videos = $query->orderByDesc('date_created')->paginate(24));
        }
    }

    public function mount($playlist, $videos, $playlists_filter_by=null, $disable_filter_by_playlists=true) {
        // Init
        $this->livewire_persist = 
        [
            'playlists_filter_by_ids' => [], 
            'playlist_url_id' => null, 
            'videos_all_ids' => []
        ];

        // Check have we got a playlist filter definition
        if($disable_filter_by_playlists != true) {
            $disable_filter_by_playlists = (bool) $disable_filter_by_playlists;
            $this->livewire_persist['playlists_filter_by_ids'] = $playlists_filter_by->pluck('id');
        }
        
        // Persist the playlist and video details
        $this->livewire_persist['playlist_url_id'] = $playlist->url_id;
        $this->livewire_persist['videos_all_ids'] = $videos->pluck('id');
    }

    public function render()
    {
        return view('livewire.video-playlist');
    }
}
