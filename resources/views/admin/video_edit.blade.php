@php
    use Ramsey\Uuid\Uuid;

    $session_uuid = Uuid::uuid4();
@endphp

<x-app-layout>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <link href="https://releases.transloadit.com/uppy/v3.3.0/uppy.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- Ensure jQuery is loaded first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://releases.transloadit.com/uppy/v3.3.0/uppy.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            ADMIN: Edit Video
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">

                <x-validation-errors class="mb-4" />

                <form id="video_upload_form" method="POST" action="{{ route('videos.update', $video->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <div>
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name', $video->name) }}" required autofocus />
                    </div>

                    @if($video && $video->cdn_id)
                        <div class="max-w-7xl mx-auto h-[400px] mt-5" id="video-player">
                            <iframe 
                                src="https://customer-rsp77k1og7ulg0vt.cloudflarestream.com/{{ $video->cdn_id }}/iframe?poster=https%3A%2F%2Fcustomer-rsp77k1og7ulg0vt.cloudflarestream.com%2F{{ $video->cdn_id }}%2Fthumbnails%2Fthumbnail.jpg%3Ftime%3D%26height%3D600" 
                                allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" 
                                allowfullscreen="true" 
                                class="h-full w-full">
                            </iframe>
                        </div>
                    @else
                        <p id="video-not-found" class="text-red-500">Video not found or CDN ID is missing.</p>
                    @endif
                    <div id="uppy-area" class="py-4 w-full"></div>

                    <div>
                        <div id="description_quill_editor">{!! old('description', $video->description) !!}</div>
                        <script>
                            var quill = new Quill('#description_quill_editor', {
                                theme: 'snow'
                            });

                            document.getElementById("video_upload_form").onsubmit = function() {
                                document.querySelector('input[name=description]').value = quill.root.innerHTML;
                            }
                        </script>
                        <input type="hidden" name="description" />
                    </div>

                    <div class="mt-4">
                        @php
                            $videoTags = json_decode($video->tags, true);
                        @endphp
                        <select class="js-example-tokenizer block mt-1 w-full" name="tags[]" multiple="multiple">
                                @foreach($allTags as $tag)
                                    <option value="{{ $tag }}" {{ is_array($videoTags) && in_array($tag, $videoTags) ? 'selected' : '' }}>
                                        {{ $tag }}
                                    </option>
                                @endforeach
                        </select>
                    </div>

                    <div class=" grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-2 mt-4">

                        <div>
                            <select id="audience" name="audience" required class="border-gray-300 focus:border-scdefault-300 focus:ring focus:ring-scdefault-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full">
                                <option value="delta" {{ old('audience', json_decode($video->audience, true)[0] ?? '') == 'delta' ? 'selected' : '' }}>Delta</option>
                                <option value="precall" {{ old('audience', json_decode($video->audience, true)[0] ?? '') == 'precall' ? 'selected' : '' }}>Precall</option>
                            </select>
                        </div>

                        <div>
                            @php
                                $playlists = \App\Http\Controllers\VideoPlaylistController::getAllPlaylists();
                                $selectedPlaylist = json_decode($video->playlist, true);
                            @endphp
                            <select id="playlist" name="playlist" required class="border-gray-300 focus:border-scdefault-300 focus:ring focus:ring-scdefault-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full">
                                @foreach($playlists as $playlist)
                                    <option value="{{ $playlist->url_id }}" {{ isset($selectedPlaylist[$playlist->url_id]) ? 'selected' : '' }}>
                                        {{ $playlist->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input id="release_relative_day" class="block mt-1 w-full" type="number" name="release_relative_day" value="{{ old('release_relative_day', $video->release_relative_day) }}" />
                        </div>

                        <div class=" flex items-center gap-2">
                            <label for="release_relative_persistent">
                                <input type="checkbox" id="release_relative_persistent" name="release_relative_persistent" {{ old('release_relative_persistent', $video->release_relative_persistent) ? 'checked' : '' }} />
                                Persist after relative release?
                            </label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input id="coverimage_file" class="block mt-1 w-full" type="file" name="coverimage_file" onchange="previewImage(event)" />
                        @if(!empty($video->coverimage_path))
                            <img id="coverimage_preview" src="{{ $video->coverimage_path }}" alt="Current Image" class="mb-4" style="max-width: 15%; height: auto;">
                        @else
                            <img id="coverimage_preview" src="" alt="Preview Image" class="mb-4" style="max-width: 200px;">
                        @endif
                    </div>                 


                    <input type="hidden" name="session_uuid" value="{{ $session_uuid }}">

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Update') }}
                        </x-button>
                    </div>
                </form>

                <script type="module">
                    import {Uppy, Dashboard, Tus} from "https://releases.transloadit.com/uppy/v3.3.0/uppy.min.mjs";

                    var uppy = new Uppy({
                        restrictions: {
                            maxNumberOfFiles: 1,
                            minNumberOfFiles: 1
                        }
                    })
                    .use(Dashboard, {
                        inline: true,
                        target: '#uppy-area'
                    })
                    .use(Tus, {
                        endpoint: '{{ env('APP_URL') }}admin/video/cloudflare_get_tusurl?session_uuid={{ $session_uuid }}',
                        chunkSize: 50 * 1024 * 1024
                    });

                    uppy.on('complete', (result) => {
                        console.log('Upload complete! We’ve uploaded these files:', result.successful);
                    });

                    // Initialize Select2 Tokenizer
                    $(document).ready(function() {
                        $('.js-example-tokenizer').select2({
                            tags: true,
                            tokenSeparators: [',', ' ']
                        });
                    });
                </script>

                <script>
                    function previewImage(event) {
                        var coverimage_preview = document.getElementById('coverimage_preview');
                        coverimage_preview.src = URL.createObjectURL(event.target.files[0]);
                        coverimage_preview.onload = function() {
                            URL.revokeObjectURL(coverimage_preview.src);
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
