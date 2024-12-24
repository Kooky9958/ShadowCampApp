<x-app-layout>
    <x-slot name="header">
        

        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            ADMIN: Upload Resource
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">

                <x-validation-errors class="mb-4" />

                <form method="POST" action="{{ route('submit_admin_resource_upload') }}" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <x-label for="name" value="{{ __('Name') }}" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" required autofocus />
                    </div>

                    <div>
                        <x-label for="description" value="{{ __('Description') }}" />
                        <x-input id="description" class="block mt-1 w-full" type="text" name="description" required />
                    </div>

                    <div>
                        <x-label for="type" value="{{ __('Type') }}" />                     
                        <select id="type" name="type" required class="border-gray-300 focus:border-scdefault-300 focus:ring focus:ring-scdefault-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full">
                            <option value="image">image</option>';
                            <option value="pdf">pdf</option>';
                            <option value="video">video</option>';
                        </select>
                    </div>

                    <div>
                        <x-label for="resource_file" value="{{ __('Resource File') }}" />
                        <x-input id="resource_file" class="block mt-1 w-full" type="file" name="resource_file" required />
                    </div>

                    <div>
                        <x-label for="audience" value="{{ __('Audience') }}" />
                        @include('includes.audience_select')
                    </div>                  

                    <div>
                        <x-label for="resource_list" value="{{ __('Resource List') }}" />
                        @include('includes.resource_list_select')
                    </div>
                    
                    <div>
                        <x-label for="resource_list_pos" value="{{ __('Resource List Position') }}" />
                        <x-input id="resource_list_pos" class="block mt-1 w-full" type="number" name="resource_list_pos" />
                    </div>

                    <div>
                        <x-label for="coverimage_file" value="{{ __('Cover image file') }}" />
                        <x-input id="coverimage_file" class="block mt-1 w-full" type="file" name="coverimage_file" required />
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
                            {{ __('Upload') }}
                        </x-button>
                    </div>
                </form>
            </div> 
        </div>
    </div>

</x-app-layout>