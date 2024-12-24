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
            ADMIN: Upload Video
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">

                <x-validation-errors class="mb-4" />

                <form id="video_upload_form" method="POST" action="{{ route('submit_admin_video_upload') }}" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" placeholder="Enter video name" required autofocus />
                    </div>

                    <div id="uppy-area" class="py-4"></div>

                    <div>
                        <div id="description_quill_editor"></div>
                        <input type="hidden" name="description"/>
                    </div>

                    <div class="mt-4">
                        <select class="js-example-tokenizer block mt-1 w-full" name="tags[]" multiple="multiple">
                            @if(isset($existingTags) && $existingTags->isNotEmpty())
                                @foreach($existingTags as $tag)
                                    <option value="{{ $tag }}">{{ $tag }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-2 mt-4">
                        <div>
                            @include('includes.audience_select')
                        </div>
                        <div>
                            @include('includes.playlist_select')
                        </div>
                        <div>
                            <x-input id="release_relative_day" class="block mt-1 w-full" type="number" name="release_relative_day" placeholder="Relative Release Day"/>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-checkbox id="release_relative_persistent" name="release_relative_persistent" />
                            <x-label for="release_relative_persistent" value="{{ __('Persist after relative release?') }}" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input id="coverimage_file" class="block mt-1 w-full" type="file" name="coverimage_file" />
                    </div>

                    <input type="hidden" name="session_uuid" value="{{ $session_uuid }}">
                    @if (request('context')=='edit_playlist')
                        <input type="hidden" name="is_playlist" value="1">                        
                    @endif

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Save') }}
                        </x-button>
                    </div>
                </form>

                <script>
                    var quill = new Quill('#description_quill_editor', {
                        theme: 'snow'
                    });

                    document.getElementById("video_upload_form").onsubmit = function() {
                        var descriptionInput = document.querySelector('input[name=description]');
                        descriptionInput.value = quill.root.innerHTML;
                        return true;
                    };
                </script>

                <script type="module">
                    import { Uppy, Dashboard, Tus } from "https://releases.transloadit.com/uppy/v3.3.0/uppy.min.mjs";

                    const uppy = new Uppy({
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
                        endpoint: `{{ env('APP_URL') }}admin/video/cloudflare_get_tusurl?session_uuid={{ $session_uuid }}`,
                        chunkSize: 50 * 1024 * 1024
                    });

                    uppy.on('complete', (result) => {
                        console.log('Upload complete! We’ve uploaded these files:', result.successful);
                    });

                    $(document).ready(function() {
                        $('.js-example-tokenizer').select2({
                            tags: true,
                            tokenSeparators: [',', ' ']
                        });
                    });
                </script>
            </div> 
        </div>
    </div>

</x-app-layout>