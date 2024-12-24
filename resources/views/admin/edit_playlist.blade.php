@php
    use Ramsey\Uuid\Uuid;

    $session_uuid = Uuid::uuid4();
@endphp

<x-app-layout>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <link href="https://releases.transloadit.com/uppy/v3.3.0/uppy.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- Ensure jQuery is loaded first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://releases.transloadit.com/uppy/v3.3.0/uppy.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            ADMIN: Upload VideoPlaylist
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">

                <x-validation-errors class="mb-4" />

                <form id="video_upload_form" method="POST" action="{{ route('video_playlist.update', $playlist->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <div>
                        <x-label for="name" value="{{ __('Name') }}" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name"
                            value="{{ old('name', $playlist->name) }}" required autofocus />
                    </div>

                    <div>
                        <x-label for="description" value="{{ __('Description') }}" />
                        <div id="description_quill_editor">{!! old('description', $playlist->description) !!}</div>
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

                    <div>
                        <x-label for="tags" value="{{ __('Tags') }}" />
                        @php
                            $videoplaylistTags = json_decode($playlist->tags, true);
                        @endphp
                        <select class="js-example-tokenizer block mt-1 w-full" name="tags[]" multiple="multiple">
                            @foreach ($allTags as $tag)
                                <option value="{{ $tag }}"
                                    {{ is_array($videoplaylistTags) && in_array($tag, $videoplaylistTags) ? 'selected' : '' }}>
                                    {{ $tag }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-label for="audience" value="{{ __('Audience') }}" />
                        <select id="audience" name="audience" required
                            class="border-gray-300 focus:border-scdefault-300 focus:ring focus:ring-scdefault-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full">
                            <option value="delta"
                                {{ old('audience', json_decode($playlist->audience, true)[0] ?? '') == 'delta' ? 'selected' : '' }}>
                                Delta</option>
                            <option value="precall"
                                {{ old('audience', json_decode($playlist->audience, true)[0] ?? '') == 'precall' ? 'selected' : '' }}>
                                Precall</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="coverimage_file" value="{{ __('Cover image file') }}" />
                        <x-input id="coverimage_file" class="block mt-1 w-full" type="file" name="coverimage_file"
                            onchange="previewImage(event)" />
                        @if (!empty($playlist->coverimage_path))
                            <img id="coverimage_preview" src="{{ $playlist->coverimage_path }}"
                                alt="Current Image" class="mb-4" style="max-width: 15%; height: auto;">
                        @else
                            <img id="coverimage_preview" src="" alt="Preview Image" class="mb-4"
                                style="max-width: 200px;">
                        @endif
                    </div>

                    <div>
                        <x-label for="release_relative_day" value="{{ __('Relative Release Day') }}" />
                        <x-input id="release_relative_day" class="block mt-1 w-full" type="number"
                            name="release_relative_day"
                            value="{{ old('release_relative_day', $playlist->release_relative_day) }}" />
                    </div>

                    <input type="hidden" name="session_uuid" value="{{ $session_uuid }}">

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Update') }}
                        </x-button>
                    </div>
                </form>

                <script type="module">
                    import {
                        Uppy,
                        Dashboard,
                        Tus
                    } from "https://releases.transloadit.com/uppy/v3.3.0/uppy.min.mjs";

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
                </script>

                <!-- Initialization Script -->
                <script>
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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-10">Add Existing Video</h2>

                <!-- Form to add videos -->
                <form action="{{ route('playlists.video_update', $playlist->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="">
                        <label for="video_id" class="block text-sm font-medium text-gray-700 ">Select Existing
                            Video</label>
                        <div class="flex justify-between items-center mt-1">
                            <div class="w-full">
                                <select name="video_id" id="video_id"
                                    class="js-example-tokenizer block w-full border-gray-300 rounded-md shadow-sm text-dark">
                                    @foreach ($allVideos as $video)
                                        <option value="{{ $video->id }}">{{ $video->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center whitespace-nowrap">
                                <x-button class="ml-4">
                                    {{ __('Add Video') }}
                                </x-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-10">Videos in Playlist</h2>

                @if ($videos->isEmpty())
                    <p>No videos found in this playlist.</p>
                @else
                    <table class="w-full bg-white border border-gray-200">
                        <thead>
                            <tr
                                class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 mt-5">
                                <th class="py-2 px-4 text-left"></th>
                                <th class="py-2 px-4 text-left">Position</th>
                                <th class="py-2 px-4 text-left">Cover Image</th>
                                <th class="py-2 px-4 text-left">Title</th>
                                <th class="py-2 px-4 text-left">Description</th>
                                <th class="py-2 px-4 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="video-list" ondragover="handleDragOver(event)">
                            @foreach ($videos as $video)
                                <tr id="video-{{ $video->id }}" class="border-b bg-white" draggable="true" data-id="{{ $video->id }}"
                                    ondragstart="handleDragStart(event)" ondrop="handleDrop(event)">
                                    <td class="py-2 px-4 cursor-move text-center">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </td>
                                    <td class="py-2 px-4 text-center">{{ $video->position }}</td> 
                                    <td class="py-2 px-4">
                                        <img src="{{ $video->coverimage_path }}"
                                            alt="Cover Image" class="w-16 h-auto">
                                    </td>
                                    <td class="py-2 px-4">
                                        <strong>
                                            <a href="{{ url('/admin/videos/' . $video->id . '/edit') }}" class="underline">
                                                {{ $video->name }}
                                            </a>
                                        </strong>
                                    </td>
                                    <td class="py-2 px-4">
                                        <div>{!! $video->description !!}</div>
                                    </td>
                                    <td class="py-2 px-4">
                                        <button class="text-red-500 hover:text-red-700"
                                            title="Remove video from playlist"
                                            onclick="removeVideo({{ $video->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Links -->
                    <div class="mt-4">
                        {{ $videos->links() }}
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button class="ml-4" onclick="addMoreVideos()">
                    {{ __('Add More Videos') }}
                </x-button>
            </div>

            <script>
                function handleDragStart(e) {
                    e.dataTransfer.setData('text/plain', e.target.id);
                    e.target.classList.add('dragging');
                }

                function handleDragOver(e) {
                    e.preventDefault();
                }

                function handleDrop(e) {
                    e.preventDefault();
                    const draggedId = e.dataTransfer.getData('text/plain');
                    const draggedElement = document.getElementById(draggedId);
                    const targetElement = e.target.closest('tr');

                    if (targetElement && draggedElement !== targetElement) {
                        const bounding = targetElement.getBoundingClientRect();
                        const offset = bounding.y + bounding.height / 2; // Middle of the target element

                        // Insert the dragged element before or after the target element based on the mouse position
                        if (e.clientY < offset) {
                            // Drop above
                            targetElement.parentNode.insertBefore(draggedElement, targetElement);
                        } else {
                            // Drop below
                            targetElement.parentNode.insertBefore(draggedElement, targetElement.nextSibling);
                        }

                        saveNewOrder(); // Call function to save the new order
                    }
                }

            </script>
            <script>
                $(function () {
                    $("#video-list").sortable({
                        placeholder: "placeholder",
                        update: function (event, ui) {
                            let order = [];
                            $("#video-list tr").each(function (index) {
                                order.push({
                                    id: $(this).data("id"),
                                    position: index + 1,
                                    page: {{request()->get('page') ? request()->get('page') : 1 }},
                                });
                            });
                            saveNewOrder(order);
                        }
                    }).disableSelection();
                });
            
                // Function to save the new order of the videos
                function saveNewOrder(order) {
                    $.ajax({
                        url: '{{ route('save_video_order') }}',
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "order": order
                        },
                        success: function (response) {
                            console.log('response', response);
                        },
                        error: function () {
                            console.log('error', error);
                        }
                    });
                }
            </script>

            <script>
                function removeVideo(videoId) {
                    // Confirm before deleting
                    if (confirm('Are you sure you want to remove this video?')) {
                        $.ajax({
                            url: `/remove-video/${videoId}/{{ $playlist->id }}`,
                            type: 'DELETE',
                            data: {
                                "_token": "{{ csrf_token() }}" // Include CSRF token
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Remove the video row from the table
                                    const videoRow = document.getElementById(`video-${videoId}`);
                                    if (videoRow) {
                                        videoRow.remove();
                                    }
                                    console.log('Video removed successfully');
                                } else {
                                    console.error('Failed to remove video:', response.message);
                                }
                            },
                            error: function(error) {
                                console.error('Error occurred while removing video:', error);
                            }
                        });
                    }
                }

                function addMoreVideos() {
                    // Logic to add more videos
                    console.log('Add more videos');
                    window.location.href = '/admin/video/upload?context=edit_playlist';
                }
            </script>
        </div>
    </div>
</x-app-layout>
