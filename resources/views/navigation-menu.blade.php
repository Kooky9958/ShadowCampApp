@php
    //// Get account and check is the user subscribed
    $got_session_account = \App\Models\Account::getSessionAccount();
    $account = $got_session_account['account'] ?? null; // Ensure $account is not null

    $is_subscribed_precall = $account ? $account->hasActiveSubTo('camp_precall') : false;
    $is_subscribed_delta = $account ? ($account->hasActiveSubTo('camp_delta_migrate') || $account->hasActiveSubTo('camp_delta9')) : false;
    $is_subscribed_general = $is_subscribed_precall || $is_subscribed_delta;
@endphp

<nav x-data="{ open: false }" class="bg-sc-headfoot-1 border-b-4 border-sc-grey-1 fixed top-0 left-0 right-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="mx-auto sm:mx-0 flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <div class="w-6 sm:w-0"></div>
                    <a href="{{ route('dashboard') }}">
                        <x-application-mark class="block h-8 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
                
                @if ($is_subscribed_general)
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <x-nav-link href="{{ route('resources') }}" :active="request()->routeIs('resource_list.list')">
                            {{ __('Resources') }}
                        </x-nav-link>
                    </div>
                @endif
                
                @if ($is_subscribed_general)
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <x-nav-link href="{{ route('workouts') }}" :active="request()->routeIs('workouts')">
                            {{ __('Videos') }}
                        </x-nav-link>
                    </div>
                @endif

                @if ($is_subscribed_general)
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link href="https://shadowcamp.gquad.shop?auth_gc=bearran">
                        {{ __('Shop') }}
                    </x-nav-link>
                </div>
                @endif

                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link href="{{ route('help') }}" :active="request()->routeIs('help')">
                        {{ __('Help') }}
                    </x-nav-link>
                </div>

            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- ADMIN Dropdown -->
                @if(App\Models\User::isAdmin())
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex place-items-center">
                        <x-dropdown>
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                        ADMIN

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link href="{{ route('admin.dashboard') }}">
                                    {{ __('DASHBOARD') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.video_upload') }}">
                                    {{ __('Upload Video') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.upload_video_event') }}">
                                    {{ __('Live Events') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.upload_availability') }}">
                                    {{ __('Availability') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.create_playlist') }}">
                                    {{ __('Create Playlist') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.resource_upload') }}">
                                    {{ __('Upload Resource') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.create_resource_list') }}">
                                    {{ __('Create Resource List') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif

                <!-- Settings Dropdown -->
                <div class="ml-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                        {{ Auth::user()->name }}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __('Manage Account') }}
                            </div>

                            {{-- <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link> --}}

                            {{-- <x-dropdown-link href="{{ route('billing') }}">
                                {{ __('Billing') }}
                            </x-dropdown-link> --}}

                        @if ($is_subscribed_general)
                            <x-dropdown-link href="{{ route('referral') }}">
                                {{ __('Referrals') }}
                            </x-dropdown-link>
                        @endif

                            <div class="border-t border-gray-100"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <x-dropdown-link href="{{ route('logout') }}"
                                         @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-sc-orange-9 transition">
                    <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        @if ($is_subscribed_general)
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link href="{{ route('resources') }}" :active="request()->routeIs('resources')">
                    {{ __('Resources') }}
                </x-responsive-nav-link>
            </div>
        @endif

        @if ($is_subscribed_general)
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link href="{{ route('workouts') }}" :active="request()->routeIs('workouts')">
                    {{ __('Videos') }}
                </x-responsive-nav-link>
            </div>

            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link href="https://shadowcamp.gquad.shop?auth_gc=bearran">
                    {{ __('Shop') }}
                </x-responsive-nav-link>
            </div>
        @endif

        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('help') }}" :active="request()->routeIs('help')">
                {{ __('Help') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 mr-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                {{-- <x-responsive-nav-link href="{{ route('billing') }}" :active="request()->routeIs('billing')">
                    {{ __('Billing') }}
                </x-responsive-nav-link> --}}

            @if ($is_subscribed_general)
                <x-responsive-nav-link href="{{ route('referral') }}" :active="request()->routeIs('referral')">
                    {{ __('Referrals') }}
                </x-responsive-nav-link>
            @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}"
                                   @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>