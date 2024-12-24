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
            ADMIN: Edit Resource
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">

                <x-validation-errors class="mb-4" />

                <form id="video_upload_form" method="POST" action="{{ route('resource.update', $resource->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <div>
                        <x-label for="name" value="{{ __('Name') }}" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name', $resource->name) }}" required autofocus />
                    </div>
                
                    <div>
                        <x-label for="description" value="{{ __('Description') }}" />
                        <x-input id="description" class="block mt-1 w-full" type="text" name="description" value="{{ old('description', $resource->description) }}" required />
                    </div>
                
                    <div>
                        <x-label for="type" value="{{ __('Type') }}" />
                        <select id="type" name="type" required class="border-gray-300 focus:border-scdefault-300 focus:ring focus:ring-scdefault-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full">
                            <option value="image" {{ $resource->type == 'image' ? 'selected' : '' }}>image</option>
                            <option value="pdf" {{ $resource->type == 'pdf' ? 'selected' : '' }}>pdf</option>
                            <option value="video" {{ $resource->type == 'video' ? 'selected' : '' }}>video</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="resource_file" value="{{ __('Resource File') }}" />
                        <x-input id="resource_file" class="block mt-1 w-full" type="file" name="resource_file" onchange="previewImage(event, 'resourceimage_preview')" />
                        @if(!empty($resource->resource_location))
                            @if ($resource->type === 'pdf')
                                <a href="{{ asset('storage/' . $resource->resource_location) }}" target="_blank" class="text-blue-600 underline">
                                    View PDF
                                </a>
                            @elseif ($resource->type === 'video')
                                <div class="max-w-7xl mx-auto h-[400px] mt-5" id="video-player">
                                    <iframe 
                                src="https://customer-rsp77k1og7ulg0vt.cloudflarestream.com/{{ $resource->resource_location }}/iframe?poster=https%3A%2F%2Fcustomer-rsp77k1og7ulg0vt.cloudflarestream.com%2F{{ $resource->resource_location }}%2Fthumbnails%2Fthumbnail.jpg%3Ftime%3D%26height%3D600" 
                                allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" 
                                allowfullscreen="true" 
                                class="h-full w-full">
                            </iframe>
                                </div>
                            @else
                                <img id="resourceimage_preview" src="{{ $resource->resource_location }}" alt="Current Resource Image" class="mb-4" style="max-width: 15%; height: auto;">
                            @endif
                        @else
                            <img id="resourceimage_preview" src="" alt="Preview Image" class="mb-4" style="max-width: 200px;">
                        @endif
                    </div> 

                    <div>
                        <x-label for="audience" value="{{ __('Audience') }}" />
                        <select id="audience" name="audience" required class="border-gray-300 focus:border-scdefault-300 focus:ring focus:ring-scdefault-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full">
                            <option value="delta" {{ old('audience', json_decode($resource->audience, true)[0] ?? '') == 'delta' ? 'selected' : '' }}>Delta</option>
                            <option value="precall" {{ old('audience', json_decode($resource->audience, true)[0] ?? '') == 'precall' ? 'selected' : '' }}>Precall</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="resource_list" value="{{ __('Resource List') }}" />
                        @php
                            // Get all resource lists
                            $resourceLists = \App\Http\Controllers\ProductContentResourceListController::getAllResourceLists();
                            
                            // Decode the current resource's resource_list JSON
                            $currentResourceListData = json_decode($resource->resource_list, true);

                            // Extract keys and check if decoding was successful
                            $selectedResourceIds = [];
                            if (json_last_error() === JSON_ERROR_NONE && is_array($currentResourceListData)) {
                                $selectedResourceIds = array_keys($currentResourceListData);
                            }
                        @endphp

                        <select id="resource_list" name="resource_list" required class="border-gray-300 focus:border-scdefault-300 focus:ring focus:ring-scdefault-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full">
                            @foreach($resourceLists as $resource_list)
                                <option value="{{ $resource_list->url_id }}" {{ in_array($resource_list->url_id, $selectedResourceIds) ? 'selected' : '' }}>
                                    {{ $resource_list->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-label for="resource_list_pos" value="{{ __('Resource List Position') }}" />
                        @php
                            // Decode the JSON string to an associative array
                            $resourceListData = json_decode($resource->resource_list, true);
                            
                            // Extract the position value if it exists
                            $positionValue = !empty($resourceListData) ? reset($resourceListData)['pos'] : null;
                        @endphp
                        <x-input id="resource_list_pos" class="block mt-1 w-full" type="number" name="resource_list_pos" value="{{ old('resource_list_pos', $positionValue) }}" />
                    </div>

                    <div>
                        <x-label for="coverimage_file" value="{{ __('Cover Image File') }}" />
                        <x-input id="coverimage_file" class="block mt-1 w-full" type="file" name="coverimage_file" onchange="previewImage(event, 'coverimage_preview')" />
                        @if(!empty($resource->coverimage_path))
                            <img id="coverimage_preview" src="{{ $resource->coverimage_path }}" alt="Current Cover Image" class="mb-4" style="max-width: 15%; height: auto;">
                        @else
                            <img id="coverimage_preview" src="" alt="Preview Image" class="mb-4" style="max-width: 200px;">
                        @endif
                    </div>  
                
                    <div>
                        <x-label for="release_relative_day" value="{{ __('Relative Release Day') }}" />
                        <x-input id="release_relative_day" class="block mt-1 w-full" type="number" name="release_relative_day" value="{{ old('release_relative_day', $resource->release_relative_day) }}" />
                    </div>
                
                    <div>
                        <x-label for="release_relative_persistent" value="{{ __('Persist after relative release?') }}" />
                        <input type="checkbox" id="release_relative_persistent" name="release_relative_persistent" {{ old('release_relative_persistent', $resource->release_relative_persistent) ? 'checked' : '' }} />
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
                function previewImage(event, previewElementId) {
                    const previewElement = document.getElementById(previewElementId);
                    if (event.target.files.length > 0) {
                        previewElement.src = URL.createObjectURL(event.target.files[0]);
                        previewElement.onload = function() {
                            URL.revokeObjectURL(previewElement.src); // Free up memory
                        }
                    } else {
                        previewElement.src = ''; // Reset if no file is selected
                    }
                }
            </script>
            </div>
        </div>
    </div>
</x-app-layout>
