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
            ADMIN: Create Playlist
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">

                <x-validation-errors class="mb-4" />

                <form id="video_playlist_upload_form" method="POST" action="{{ route('submit_admin_create_playlist') }}" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <x-label for="name" value="{{ __('Name') }}" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" required autofocus />
                    </div>

                    <div>
                        <x-label value="{{ __('Description') }}" />
                        <div id="description_quill_editor"></div>
                        <script>
                            var quill = new Quill('#description_quill_editor', {
                                theme: 'snow'
                            });

                            var form = document.getElementById("video_playlist_upload_form");

                            form.onsubmit = function() {
                                var name = document.querySelector('input[name=description]');
                                name.value = quill.root.innerHTML;
                                return true;
                            }
                        </script>
                        <input type="hidden" name="description"/>
                    </div>

                    <!-- Tags Field -->
                    <div>
                        <x-label for="tags" value="{{ __('Tags') }}" />
                        <select class="js-example-tokenizer block mt-1 w-full" name="tags[]" multiple="multiple">
                            @if(isset($existingTags) && $existingTags->isNotEmpty())
                                @foreach($existingTags as $tag)
                                    <option value="{{ $tag }}">{{ $tag }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <div>
                        <x-label for="audience" value="{{ __('Audience') }}" />
                        @include('includes.audience_select')
                    </div>

                    <div>
                        <x-label for="coverimage_file" value="{{ __('Cover image file') }}" />
                        <x-input id="coverimage_file" class="block mt-1 w-full" type="file" name="coverimage_file" />
                    </div>

                    <div>
                        <x-label for="release_relative_day" value="{{ __('Relative Release Day') }}" />
                        <x-input id="release_relative_day" class="block mt-1 w-full" type="number" name="release_relative_day" />
                    </div>

                    <div>
                        <x-label for="release_relative_persistent" value="{{ __('Persist after relative release?') }}" />
                        <x-checkbox id="release_relative_persistent" name="release_relative_persistent" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Create') }}
                        </x-button>
                    </div>
                </form>

                <script>
                    // Initialize Select2 Tokenizer
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