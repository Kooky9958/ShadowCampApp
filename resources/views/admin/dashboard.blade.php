<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            ADMIN: Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                <h3 class="font-bold text-xl">View:</h3>
                <p>
                    <a href="/admin/crud/list/Account">
                        <x-button>
                            {{ __('Accounts') }}
                        </x-button>
                    </a>
                    <a href="/admin/crud/list/User">
                        <x-button>
                            {{ __('Users') }}
                        </x-button>
                    </a>
                    <a href="/admin/crud/list/Transaction">
                        <x-button>
                            {{ __('Transasctions') }}
                        </x-button>
                    </a>
                    <a href="/admin/crud/list/ProductContentResource">
                        <x-button>
                            {{ __('Resources') }}
                        </x-button>
                    </a>
                    <a href="/admin/crud/list/Video">
                        <x-button>
                            {{ __('Videos') }}
                        </x-button>
                    </a>
                    <a href="/admin/crud/list/VideoPlaylist">
                        <x-button>
                            {{ __('Playlists') }}
                        </x-button>
                    </a>
                    <a href="/admin/crud/list/VideoEvent">
                        <x-button>
                            {{ __('Live Events') }}
                        </x-button>
                    </a>
                </p>
                <h3 class="font-bold text-xl">Report:</h3>
                <p>
                    <a href="/admin/crud/report/lapsed_delta">
                        <x-button>
                            {{ __('Delta Lapsed Customers') }}
                        </x-button>
                    </a>
                    <a href="/admin/crud/report/lost_precall">
                        <x-button>
                            {{ __('Precall Lost Customers') }}
                        </x-button>
                    </a>
                    <a href="/admin/crud/report/new_delta_migrate">
                        <x-button>
                            {{ __('New Delta Migrate Customers') }}
                        </x-button>
                    </a>
                    <br/>
                    <br/>
                    <a href="/admin/crud/report/current_delta">
                        <x-button>
                            {{ __('Current Delta Customers') }}
                        </x-button>
                    </a>
                    <a href="/admin/crud/report/current_precall">
                        <x-button>
                            {{ __('Current Precall Customers') }}
                        </x-button>
                    </a>
                </p>
            </div>
        </div>
    </div>

</x-app-layout>