<select id="playlist" name="playlist" required class="border-gray-300 focus:border-scdefault-300 focus:ring focus:ring-scdefault-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full">
    <option value="">Playlist</option>
    @php
        foreach(\App\Http\Controllers\VideoPlaylistController::getAllPlaylists() as $playlist) {
            echo '<option value="'.$playlist->url_id.'">'.$playlist->name.'</option>';
        }
    @endphp
</select>