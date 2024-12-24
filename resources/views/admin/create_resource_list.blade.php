<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            ADMIN: Create Resource List
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">

                <x-validation-errors class="mb-4" />

                <form method="POST" action="{{ route('submit_admin_create_resource_list') }}">
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
                        <x-label for="audience" value="{{ __('Audience') }}" />
                        @include('includes.audience_select')
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
            </div> 
        </div>
    </div>

</x-app-layout>