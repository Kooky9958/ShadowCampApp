<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('User Profile') }}
    </h2>
</x-slot>
<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
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
                            <x-label for="photo" value="{{ __('Photo') }}" />

                            <!-- Current Profile Photo -->
                            <div class="mt-2" x-show="! photoPreview">
                                <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}"
                                    class="rounded-full h-20 w-20 object-cover">
                            </div>
                        </div>
                    @endif

                    <!-- Name -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="name" value="{{ __('Name') }}" />
                        <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" required
                            autocomplete="name" />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="email" value="{{ __('Email') }}" />
                        <x-input id="email" type="email" class="mt-1 block w-full" wire:model="email" required
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
                        <select id="gender" class="mt-1 block w-full" wire:model="gender">
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
                        <x-input id="age" type="number" class="mt-1 block w-full" wire:model="age" required />
                        <x-input-error for="age" class="mt-2" />
                    </div>

                    <!-- Height -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="height" value="{{ __('Height (cm)') }}" />
                        <x-input id="height" type="number" class="mt-1 block w-full" wire:model="height" required />
                        <x-input-error for="height" class="mt-2" />
                    </div>

                    <!-- Weight -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="weight" value="{{ __('Weight (kg)') }}" />
                        <x-input id="weight" type="number" class="mt-1 block w-full" wire:model="weight" required />
                        <x-input-error for="weight" class="mt-2" />
                    </div>

                    <!-- Address Line 1 -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="address_line1" value="{{ __('Address Line 1') }}" />
                        <textarea id="address_line1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" wire:model="address_line1"
                            required></textarea>
                        <x-input-error for="address_line1" class="mt-2" />
                    </div>

                    <!-- Country -->
                    <?php
                    $countries = $this->user->getallcountries();
                    $selectedCountry = $this->state['country'] ?? null;
                    ?>
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="country" value="{{ __('Country') }}" />
                        <select id="country" class="mt-1 block w-full js-select2-country" wire:model="country">
                            <option value="">{{ __('Select Country') }}</option>
                            @foreach ($countries as $countryName)
                                <option value="{{ $countryName }}"
                                    {{ $selectedCountry == $countryName ? 'selected' : '' }}>
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
                        <select id="region" class="mt-1 block w-full js-select2-region" wire:model="region"
                            required>
                            @foreach ($regions as $regionName)
                                <option value="{{ $regionName }}"
                                    {{ $selectedRegion == $regionName ? 'selected' : '' }}>
                                    {{ $regionName }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="region" class="mt-2" />
                    </div>

                    <!-- City -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="city" value="{{ __('City') }}" />
                        <x-input id="city" type="text" class="mt-1 block w-full" wire:model="city"
                            required />
                        <x-input-error for="city" class="mt-2" />
                    </div>

                    <!-- Post Code -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="postcode" value="{{ __('Post Code') }}" />
                        <x-input id="postcode" type="number" class="mt-1 block w-full" wire:model="postcode"
                            required />
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
                            wire:model.defer="hobbies" multiple="multiple">
                            @foreach ($existingHobbies as $hobby)
                                <option value="{{ $hobby }}">{{ $hobby }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="hobbies" class="mt-2" />
                    </div>

                    <!-- Include Select2 CSS -->
                    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css"
                        rel="stylesheet" />
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
                                let selectedCountry = {!! json_encode($this->state['country']) !!};
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
                                let selectedRegion = {!! json_encode($this->state['region']) !!};
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
                                                var selectedRegionId = {!! json_encode($this->state['region']) !!};
                                                response.forEach(function(region) {
                                                    var isSelected = selectedRegionId == region.id ? 'selected' : '';
                                                    regionDropdown.append('<option value="' + region
                                                        .id + '" ' + isSelected + '>' + region.name + '</option>');
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

                        function handleSaveClick() {
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }
                    </script>

                </x-slot>

                {{-- <x-slot name="actions">
                    <x-action-message class="me-3" on="saved">
                        {{ __('Saved.') }}
                    </x-action-message>

                    <x-button wire:loading.attr="disabled" wire:target="photo" onclick="handleSaveClick()">
                        {{ __('Save') }}
                    </x-button>
                </x-slot> --}}
            </x-form-section>
            <x-section-border />
        @endif

         {{-- Questions --}}
         
         <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <form action="{{ route('profile_question.update') }}" method="POST" class="mt-1 text-sm text-gray-900">
                @csrf
        
                <x-form-section submit="updateProfileQuestions">
                    <x-slot name="title">
                        {{ __('Questions') }}
                    </x-slot>
        
                    <x-slot name="description">
                        {{ __('Please select all that apply.') }}
                    </x-slot>
        
                    <x-slot name="form">
                        @php
                        $goals = $profileQuestions->goals ?? [];
                        $mentalHealthIssues = $profileQuestions->mental_health_issues ?? [];
                        @endphp
                    <div class="flex flex-col gap-2">
                        <h3 class="text-lg font-medium text-gray-900 mb-1 whitespace-nowrap">What goals are you hoping to achieve at ShadowCamp?</h3>
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Understand my body" {{ in_array("Understand my body", $goals) ? 'checked' : '' }}> Understand my body</label>
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Lose weight" {{ in_array("Lose weight", $goals) ? 'checked' : '' }}> Lose weight</label>
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Gain weight" {{ in_array("Gain weight", $goals) ? 'checked' : '' }}> Gain weight</label>
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Tone up my body" {{ in_array("Tone up my body", $goals) ? 'checked' : '' }}> Tone up my body</label>
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Get more energy" {{ in_array("Get more energy", $goals) ? 'checked' : '' }}> Get more energy</label>
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Enhance my sex life" {{ in_array("Enhance my sex life", $goals) ? 'checked' : '' }}> Enhance my sex life</label>
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Improve aspects of mental health" id="mental_health" {{ in_array("Improve aspects of mental health", $goals) ? 'checked' : '' }}> Improve aspects of mental health</label>
                    
                    <!-- Conditional mental health options -->
                    <div id="mental_health_options" style="{{ in_array("Improve aspects of mental health", $goals) ? 'display: block;' : 'display: none;' }}" class="pl-5 space-y-2">
                        <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="mental_health_issues[]" value="Stress" {{ in_array("Stress", $mentalHealthIssues) ? 'checked' : '' }}> Stress</label>
                        <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="mental_health_issues[]" value="Mood fluctuations" {{ in_array("Mood fluctuations", $mentalHealthIssues) ? 'checked' : '' }}> Mood fluctuations</label>
                        <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="mental_health_issues[]" value="Anxiety" {{ in_array("Anxiety", $mentalHealthIssues) ? 'checked' : '' }}> Anxiety</label>
                        <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="mental_health_issues[]" value="Depression" {{ in_array("Depression", $mentalHealthIssues) ? 'checked' : '' }}> Depression</label>
                        <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="mental_health_issues[]" value="Low energy" {{ in_array("Low energy", $mentalHealthIssues) ? 'checked' : '' }}> Low energy</label>
                        <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="mental_health_issues[]" value="Poor self-image" {{ in_array("Poor self-image", $mentalHealthIssues) ? 'checked' : '' }}> Poor self-image</label>
                    </div>
        
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Have less mood swings" {{ in_array("Have less mood swings", $goals) ? 'checked' : '' }}> Have less mood swings</label>
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Feel positive about my body" {{ in_array("Feel positive about my body", $goals) ? 'checked' : '' }}> Feel positive about my body</label>
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Improve my sleeping" {{ in_array("Improve my sleeping", $goals) ? 'checked' : '' }}> Improve my sleeping</label>
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" value="Learn about nutrition" {{ in_array("Learn about nutrition", $goals) ? 'checked' : '' }}> Learn about nutrition</label>
                    
                    <!-- Other option -->
                    <label class="flex gap-2 items-center whitespace-nowrap"><input type="checkbox" name="goals[]" id="other_option" value="Other" {{ in_array("Other", $goals) ? 'checked' : '' }}> Other</label>
                    <input type="text" name="other_goal" id="other_goal" placeholder="Please specify" value="{{ $profileQuestions->other_goal ?? '' }}" class="w-80 border rounded-md bg-transparent" style="display: {{ in_array("Other", $goals) ? 'block' : 'none' }};">
        
                    <x-section-border />
        
                    <h3 class="text-lg font-medium text-gray-900 mb-1 whitespace-nowrap">A few more questions to help us understand you better</h3>
                    
                    <!-- Yes/No/Prefer not to answer questions -->
                    <label class="flex gap-2 items-center whitespace-nowrap font-semibold">Do you experience hair loss?</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">A <input type="radio" name="hair_loss" value="Yes" {{ $profileQuestions->hair_loss === 'Yes' ? 'checked' : '' }}> Yes</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">B <input type="radio" name="hair_loss" value="No" {{ $profileQuestions->hair_loss === 'No' ? 'checked' : '' }}> No</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">C<input type="radio" name="hair_loss" value="Prefer not to answer" {{ $profileQuestions->hair_loss === 'Prefer not to answer' ? 'checked' : '' }}> Prefer not to answer</label>
        
                    <label class="flex gap-2 items-center whitespace-nowrap font-semibold">Are you on birth control now?</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">A <input type="radio" name="birth_control" value="Yes" {{ $profileQuestions->birth_control === 'Yes' ? 'checked' : '' }}> Yes</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">B <input type="radio" name="birth_control" value="No" {{ $profileQuestions->birth_control === 'No' ? 'checked' : '' }}> No</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">C<input type="radio" name="birth_control" value="Prefer not to answer" {{ $profileQuestions->birth_control === 'Prefer not to answer' ? 'checked' : '' }}> Prefer not to answer</label>
        
                    <label class="flex gap-2 items-center whitespace-nowrap font-semibold">Do you have any reproductive health disorders (endometriosis, PCOS, etc)?</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">A <input type="radio" name="reproductive_disorder" value="Yes" {{ $profileQuestions->reproductive_disorder === 'Yes' ? 'checked' : '' }}> Yes</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">B <input type="radio" name="reproductive_disorder" value="No" {{ $profileQuestions->reproductive_disorder === 'No' ? 'checked' : '' }}> No</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">C<input type="radio" name="reproductive_disorder" value="Prefer not to answer" {{ $profileQuestions->reproductive_disorder === 'Prefer not to answer' ? 'checked' : '' }}> Prefer not to answer</label>
        
                    <label class="flex gap-2 items-center whitespace-nowrap font-semibold">Has your weight changed recently?</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">A <input type="radio" name="weight_change" value="Yes" {{ $profileQuestions->weight_change === 'Yes' ? 'checked' : '' }}> Yes</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">B <input type="radio" name="weight_change" value="No" {{ $profileQuestions->weight_change === 'No' ? 'checked' : '' }}> No</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">C<input type="radio" name="weight_change" value="Prefer not to answer" {{ $profileQuestions->weight_change === 'Prefer not to answer' ? 'checked' : '' }}> Prefer not to answer</label>
        
                    <label class="flex gap-2 items-center whitespace-nowrap font-semibold">Do you drink more than two cups of coffee a day?</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">A <input type="radio" name="coffee_consumption" value="Yes" {{ $profileQuestions->coffee_consumption === 'Yes' ? 'checked' : '' }}> Yes</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">B <input type="radio" name="coffee_consumption" value="No" {{ $profileQuestions->coffee_consumption === 'No' ? 'checked' : '' }}> No</label>
                    <label class="flex gap-2 items-center whitespace-nowrap">C<input type="radio" name="coffee_consumption" value="Prefer not to answer" {{ $profileQuestions->coffee_consumption === 'Prefer not to answer' ? 'checked' : '' }}> Prefer not to answer</label>
                    </div>
                    </x-slot>
                </x-form-section>
            </form>
        </div>
         
         <!-- JavaScript for toggling fields -->
         <script>
             document.getElementById('mental_health').addEventListener('change', function () {
                 document.getElementById('mental_health_options').style.display = this.checked ? 'block' : 'none';
             });
         
             document.getElementById('other_option').addEventListener('change', function () {
                 document.getElementById('other_goal').style.display = this.checked ? 'block' : 'none';
             });
         </script>

        <x-section-border />
        <div class="mt-10 sm:mt-0">
            <livewire:profile.logout-other-browser-sessions-form :isReadOnly="true" />
        </div>

        <!-- Display Past Payments -->
        <x-section-border />
        <h4 class="font-semibold text-lg mt-10 sm:mt-0">Past Payments</h4>
        <div class="overflow-x-auto py-5">
            <table class="table-auto w-full border-collapse border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border text-left">Date</th>
                        <th class="px-4 py-2 border text-left">Amount</th>
                        <th class="px-4 py-2 border text-left">Currency</th>
                        <th class="px-4 py-2 border text-left">Payment Method</th>
                        <th class="px-4 py-2 border text-left">Payment Provider</th>
                        <th class="px-4 py-2 border text-left">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $payment)
                        <tr>
                            <td class="border px-4 py-2">{{ $payment['date'] }}</td>
                            <td class="border px-4 py-2">${{ $payment['amount'] }}</td>
                            <td class="border px-4 py-2">{{ $payment['currency'] }}</td>
                            <td class="border px-4 py-2">{{ $payment['payment_method'] }}</td>
                            <td class="border px-4 py-2">{{ $payment['payment_provider'] }}</td>
                            <td class="border px-4 py-2">{{ $payment['description'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center border px-4 py-2">No payment history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
</div>
