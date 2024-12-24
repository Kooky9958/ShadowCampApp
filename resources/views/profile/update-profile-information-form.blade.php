{{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" /> --}}
<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account\'s profile information and email address.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{ photoName: null, photoPreview: null }" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input type="file" id="photo" class="hidden" wire:model.live="photo" x-ref="photo"
                    x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <x-label for="photo" value="{{ __('Photo') }}" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}"
                        class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                        x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('Select A New Photo') }}
                </x-secondary-button>
                <div>
                    <small class="text-red-600">Only upload maximun 2 MB Image.</small>
                </div>

                @if ($this->user->profile_photo_path)
                    <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                        {{ __('Remove Photo') }}
                    </x-secondary-button>
                @endif

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('Name') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" required
                autocomplete="name" />
            <x-input-error for="name" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required
                autocomplete="username" />
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) &&
                    !$this->user->hasVerifiedEmail())
                <p class="text-sm mt-2">
                    {{ __('Your email address is unverified.') }}

                    <button type="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        wire:click.prevent="sendEmailVerification">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-medium text-sm text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif
        </div>

        <!-- Gender -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="gender" value="{{ __('Gender') }}" />
            <select id="gender" class="mt-1 block w-full" wire:model="state.gender">
                <option value="">{{ __('Select Gender') }}</option>
                <option value="Male">{{ __('Male') }}</option>
                <option value="Female">{{ __('Female') }}</option>
                <option value="Other">{{ __('Other') }}</option>
            </select>
            <x-input-error for="gender" class="mt-2" />
        </div>

        <!-- Age -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="age" value="{{ __('Age') }}" />
            <x-input id="age" type="number" class="mt-1 block w-full" wire:model="state.age" required />
            <x-input-error for="age" class="mt-2" />
        </div>

        <!-- Height -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="height" value="{{ __('Height (cm)') }}" />
            <x-input id="height" type="number" class="mt-1 block w-full" wire:model="state.height" required />
            <x-input-error for="height" class="mt-2" />
        </div>

        <!-- Weight -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="weight" value="{{ __('Weight (kg)') }}" />
            <x-input id="weight" type="number" class="mt-1 block w-full" wire:model="state.weight" required />
            <x-input-error for="weight" class="mt-2" />
        </div>

        <!-- Address Line 1 -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="address_line1" value="{{ __('Address Line 1') }}" />
            <textarea id="address_line1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                wire:model="state.address_line1" required></textarea>
            <x-input-error for="address_line1" class="mt-2" />
        </div>

        <!-- Country -->
        <?php
        $countries = $this->user->getallcountries();
        $selectedCountry = $this->state['country'] ?? null;
        ?>
        <div class="col-span-6 sm:col-span-4">
            <x-label for="country" value="{{ __('Country') }}" />
            <select id="country" class="mt-1 block w-full js-select2-country" wire:model="state.country">
                <option value="">{{ __('Select Country') }}</option>
                @foreach ($countries as $countryName)
                    <option value="{{ $countryName }}" {{ $selectedCountry == $countryName ? 'selected' : '' }}>
                        {{ $countryName }}
                    </option>
                @endforeach
            </select>
            <x-input-error for="country" class="mt-2" />
        </div>

        <!-- Region -->
        <?php
        $regions = $this->user->getAllRegions($this->state['country'] ?? null); // Fetch regions based on selected country
        $selectedRegion = $this->state['region'] ?? null;
        ?>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="region" value="{{ __('Region') }}" />
            <select id="region" class="mt-1 block w-full js-select2-region" wire:model="state.region" required>
                @foreach ($regions as $regionName)
                    <option value="{{ $regionName }}" {{ $selectedRegion == $regionName ? 'selected' : '' }}>
                        {{ $regionName }}
                    </option>
                @endforeach
            </select>
            <x-input-error for="region" class="mt-2" />
        </div>

        <!-- City -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="city" value="{{ __('City') }}" />
            <x-input id="city" type="text" class="mt-1 block w-full" wire:model="state.city" required />
            <x-input-error for="city" class="mt-2" />
        </div>

        <!-- Post Code -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="postcode" value="{{ __('Post Code') }}" />
            <x-input id="postcode" type="number" class="mt-1 block w-full" wire:model="state.postcode" required />
            <x-input-error for="postcode" class="mt-2" />
        </div>

        <!-- Favourite Hobbies -->
        @php
            $existingHobbies = $this->user->getAllHobbies();
            $userHobbies = is_string($this->user->hobbies)
                ? json_decode($this->user->hobbies, true)
                : $this->user->hobbies;
        @endphp
        <div class="col-span-6 sm:col-span-4">
            <x-label for="hobbies" value="{{ __('Favourite Hobbies') }}" />
            <select id="hobbies" name="hobbies[]" class="js-example-tokenizer mt-1 block w-full"
                wire:model.defer="state.hobbies" multiple="multiple">
                @foreach ($existingHobbies as $hobby)
                    <option value="{{ $hobby }}">{{ $hobby }}</option>
                @endforeach
            </select>
            <x-input-error for="hobbies" class="mt-2" />
        </div>

        <!-- Include Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
        <!-- Include jQuery and Select2 JS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                function initSelect2() {
                    $('.js-example-tokenizer').select2({
                        tags: true,
                        tokenSeparators: [',', ' '],
                        placeholder: 'Select or add hobbies'
                    }).on('change', function() {
                        let selectedHobbies = $(this).val();
                        @this.set('state.hobbies', selectedHobbies, false);
                    });

                    // Pre-select the user's hobbies
                    let userHobbies = {!! json_encode($userHobbies) !!};

                    console.log('userHobbies:', userHobbies);

                    if (Array.isArray(userHobbies) && userHobbies.length > 0) {
                        userHobbies.forEach(function(hobby) {
                            if ($('.js-example-tokenizer option[value="' + hobby + '"]').length === 0) {
                                $('.js-example-tokenizer').append(new Option(hobby, hobby, true, true));
                            }
                        });
                        $('.js-example-tokenizer').val(userHobbies).trigger('change');
                    }

                    // Initialize Select2 for country
                    $('.js-select2-country').select2({
                        placeholder: 'Select a country',
                        allowClear: true
                    }).on('change', function() {
                        let selectedCountry = $(this).val();
                        @this.set('state.country', selectedCountry, false);
                    });

                    // Pre-select country
                    let selectedCountry = {!! json_encode($selectedCountry) !!};
                    console.log('selectedCountry:', selectedCountry);
                    if (selectedCountry) {
                        $('.js-select2-country').val(selectedCountry).trigger('change');
                    }

                    // Initialize Select2 for region field
                    $('.js-select2-region').select2({
                        placeholder: 'Select a region',
                        allowClear: true
                    }).on('change', function() {
                        let selectedRegion = $(this).val();
                        @this.set('state.region', selectedRegion, false); // Sync with Livewire
                    });

                    // Pre-select the region if it exists
                    let selectedRegion = {!! json_encode($selectedRegion) !!};
                    console.log('Selected Region:', selectedRegion);
                    if (selectedRegion) {
                        $('.js-select2-region').val(selectedRegion).trigger('change');
                    }
                }

                $('#country').change(function() {
                    var countryId = $(this).val();
                    var regionDropdown = $('#region');

                    // Clear existing options
                    regionDropdown.empty();
                    // regionDropdown.append('<option value="">{{ __('Select Region') }}</option>');

                    if (countryId) {
                        $.ajax({
                            url: '{{ route('regions.byCountry') }}',
                            method: 'GET',
                            data: {
                                id: countryId
                            },
                            success: function(response) {
                                if (Array.isArray(response)) {
                                    response.forEach(function(region) {
                                        regionDropdown.append('<option value="' + region
                                            .id + '">' + region.name + '</option>');
                                    });
                                } else {
                                    console.error('Invalid response format');
                                }
                            },
                            error: function() {
                                console.error('Failed to fetch regions');
                            }
                        });
                    }
                });

                initSelect2();

                document.addEventListener('livewire:load', function() {
                    Livewire.hook('message.processed', (message, component) => {
                        initSelect2();
                    });

                    Livewire.on('profileUpdated', function() {
                        window.location.reload();
                    });
                });
            });

            // function handleSaveClick() {
            //     setTimeout(() => {
            //         window.location.reload();
            //     }, 500);
            // }
        </script>

    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo" onclick="handleSaveClick()">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
