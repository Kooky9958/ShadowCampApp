@php
    use Ramsey\Uuid\Uuid;

    $session_uuid = Uuid::uuid4();
@endphp

<x-app-layout>
    <!-- Quill.js CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- Uppy CSS -->
    <link href="https://releases.transloadit.com/uppy/v3.3.0/uppy.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- Daterangepicker CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet" />

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Ensure jQuery is loaded -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Quill.js JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <!-- Uppy JS -->
    <script src="https://releases.transloadit.com/uppy/v3.3.0/uppy.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Moment.js and Daterangepicker JS -->
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <style>
        .toggle-day {
            position: relative;
            width: 40px;
            height: 20px;
            appearance: none;
            background: #ddd;
            border-radius: 20px;
            outline: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .toggle-day:checked {
            background: #4CAF50;
        }

        .toggle-day:before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            transform: scale(1.1);
            left: 0;
            transition: left 0.3s ease;
        }

        .toggle-day:checked:before {
            left: 20px;
        }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            ADMIN: Availability
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                <x-validation-errors class="mb-4" />

                <form id="video_upload_form" method="POST" action="{{ route('submit_availability') }}">
                    @csrf
                    <div class="availability-form space-y-4 mt-4">
                        <x-label for="available_day" :value="__('Available Day')" />
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <div class="day-availability flex items-center space-x-4">
                                <input type="checkbox" name="availability[{{ $day }}][enabled]" class="toggle-day" 
                                       {{ isset($availabilities[$day]) && $availabilities[$day]->status == 1 ? 'checked' : '' }}>
                                <label class="text-gray-600">{{ $day }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Save') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>