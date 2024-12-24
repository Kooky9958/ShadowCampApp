@php
    use Ramsey\Uuid\Uuid;

    $session_uuid = Uuid::uuid4();

@endphp

<x-app-layout>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <link href="https://releases.transloadit.com/uppy/v3.3.0/uppy.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- Daterangepicker CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet" />

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Ensure jQuery is loaded first-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://releases.transloadit.com/uppy/v3.3.0/uppy.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Moment.js and Daterangepicker JS -->
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            ADMIN: Video Event
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">

                <x-validation-errors class="mb-4" />

                <form id="video_upload_form" method="POST" action="{{ route('submit_event') }}">
                    @csrf

                    <div class="mt-4">
                        <x-label for="live_stream_id" :value="__('Live Stream')" />
                        <select id="live_stream_id" name="live_stream_id" class="block mt-1 w-full" required>
                            <option value="" disabled  selected>Select a Live Stream</option>
                            @foreach ($liveStreams as $stream)
                                <option value="{{ $stream->id }}">{{ $stream->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-4">
                        <x-label for="message" :value="__('Event Message')" />
                        <div id="message_quill_editor"></div>
                        <input type="hidden" name="message" id="message"/>
                    </div>

                    <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-2 mt-4">
                        <div class="mt-4">
                            <x-label for="available_date" :value="__('Available Date Range')" />
                            <x-input id="available_date" class="block mt-1 w-full" type="text" name="available_date" />
                            <input type="hidden" name="start_available_date" id="start_available_date" />
                            <input type="hidden" name="end_available_date" id="end_available_date" />
                        </div>

                        <div class="mt-4">
                            <x-label for="start_event_time" :value="__('Start Event Time')" />
                            <x-input id="start_event_time" class="block mt-1 w-full" type="time" name="start_event_time" required />
                        </div>

                        <div class="mt-4">
                            <x-label for="end_event_time" :value="__('End Event Time')" />
                            <x-input id="end_event_time" class="block mt-1 w-full" type="time" name="end_event_time" required />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4">
                            {{ __('Save') }}
                        </x-button>
                    </div>
                </form>

                <script>
                    var quill = new Quill('#message_quill_editor', {
                        theme: 'snow'
                    });

                    document.getElementById("video_upload_form").onsubmit = function() {
                        var messageInput = document.querySelector('input[name=message]');
                        messageInput.value = quill.root.innerHTML;
                        return true;
                    };

                    // Initialize date range picker on available_date field
                    $('#available_date').daterangepicker({
                        opens: 'left',
                        locale: {
                            format: 'YYYY-MM-DD'
                        },
                        minDate: moment().format('YYYY-MM-DD'),
                    }, function(start, end) {
                        // Set hidden fields with selected dates
                        $('#start_available_date').val(start.format('YYYY-MM-DD'));
                        $('#end_available_date').val(end.format('YYYY-MM-DD'));
                    });
                </script>
            </div> 
        </div>
    </div>

</x-app-layout>