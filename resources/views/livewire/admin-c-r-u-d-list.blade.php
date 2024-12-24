@php
    $model_instance = new $this->model_fqcn();
    $display_belongs_to_relations = $model_instance::getACDisplayBelongsToRelations();

    // Define custom headers for specific models
    $custom_headers = [
        'User' => [
            'email_verified_at' => 'Email Verified',
        ],
    ];

    $table_headers = array_merge($display_belongs_to_relations, $model_object_vars);

    // Apply custom header names if applicable
    $model_name = class_basename($this->model_fqcn);
    if (isset($custom_headers[$model_name])) {
        $table_headers = array_map(function ($header) use ($custom_headers, $model_name) {
            return $custom_headers[$model_name][$header] ?? $header;
        }, $table_headers);
    }
@endphp
<section class="dark:bg-gray-900 p-3 sm:p-5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <div class="mx-auto w-full px-4">
        <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
            
            @if ($model_class_name == 'Video')
                <div class="flex items-center justify-end mt-4 w-full px-2">
                    <x-button class="ml-4 " onclick="addVideos()">
                        {{ __('Add Videos') }}
                    </x-button>
                </div>
            @endif

            <script>
                function addVideos() {
                // Logic to add more videos
                console.log('Add videos');
                window.location.href = '/admin/video/upload';
            }
            </script>

            <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                @if ($model_class_name == 'User')
                    <div class="w-full md:w-7/12 flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4">
                        <select id="country" wire:model="country" class="block w-full bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                            <option value="">All Country</option>
                            @foreach ($countries as $countryOption)
                                <option value="{{ $countryOption }}">{{ $countryOption }}</option>
                            @endforeach
                        </select>
            
                        <select id="age_range" wire:model="age_range" class="block w-full bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                            <option value="">All Age</option>
                            <option value="18-25">18-25</option>
                            <option value="26-35">26-35</option>
                            <option value="36-45">36-45</option>
                            <option value="46-60">46-60</option>
                        </select>
            
                        <select id="gender" wire:model="gender" class="block w-full bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                            <option value="">All</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <div class=" flex items-center">
                            <button type="button" wire:click.prevent="exportUserResult" class="flex items-center justify-center text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">
                                Export
                            </button>
                        </div>
                    </div>
            
                @endif
            
                <div class="w-full flex justify-end">
                    @if (is_subclass_of($this->model_fqcn, \App\Interfaces\AdminCRUDSearchable::class))
                        <form class="flex items-center justify-end w-1/2" wire:submit="submit_search">
                            <label for="search_query" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="text" id="search_query" wire:model="search_query" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search">
                            </div>
                            <button type="button" wire:click.prevent="submit_search" class="flex items-center justify-center text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2 mx-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">
                                Go
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto" style="scrollbar-width: thin;">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>

                            @foreach ($table_headers as $header)
                                <th scope="col" class="px-4 py-3">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->getList() as $row)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 flex items-center justify-end">
                                    <button id="listitem-{{ $row->id }}-dropdown-button"
                                        data-dropdown-toggle="listitem-{{ $row->id }}-dropdown"
                                        class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none dark:text-gray-400 dark:hover:text-gray-100"
                                        type="button">
                                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                        </svg>
                                    </button>
                                    <div id="listitem-{{ $row->id }}-dropdown"
                                        class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                                        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                                            aria-labelledby="listitem-{{ $row->id }}-dropdown-button">
                                            <li>
                                                @if (stripos($this->model_fqcn, 'Video') === false)
                                                    <a wire:click.prevent="$dispatchTo('admin-c-r-u-d-retrieve-modal', 'show-retrieve-modal', { id: {{ $row->id }} })"
                                                        data-modal-target="retrieve-modal"
                                                        data-modal-show="retrieve-modal" href="#"
                                                        class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                        Show
                                                    </a>
                                                @endif
                                                @if (stripos($this->model_fqcn, 'User') !== false)
                                                    <form class="hover:bg-sc-bg-1 dark:hover:bg-gray-600"
                                                        action="{{ route('crud.destroy', ['model_class_name' => strtolower(class_basename($this->model_fqcn)), 'id' => $row->id]) }}"
                                                        method="POST" class="block"
                                                        onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 text-red-500 dark:text-red-400 ">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (preg_match('/\bVideo\b/i', $this->model_fqcn))
                                                    <form class="hover:bg-sc-bg-1 dark:hover:bg-gray-600"
                                                        action="{{ route('videos.edit', ['id' => $row->id]) }}"
                                                        method="GET" class="block">
                                                        @csrf
                                                        <button type="submit"
                                                            class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                            Edit
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (preg_match('/\bVideo\b/i', $this->model_fqcn))
                                                    <form class="hover:bg-sc-bg-1 dark:hover:bg-gray-600"
                                                        action="{{ route('videos.destroy', ['model_class_name' => strtolower(class_basename($this->model_fqcn)), 'id' => $row->id]) }}"
                                                        method="POST" class="block"
                                                        onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 text-red-500 dark:text-red-400 ">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                                {{-- video event --}}
                                                @if (stripos($this->model_fqcn, 'VideoEvent') !== false)
                                                    <form class="hover:bg-sc-bg-1 dark:hover:bg-gray-600"
                                                        action="{{ route('videoevents.edit', ['id' => $row->id]) }}"
                                                        method="GET" class="block">
                                                        @csrf
                                                        <button type="submit"
                                                            class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                            Edit
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (stripos($this->model_fqcn, 'VideoEvent') !== false)
                                                    <form class="hover:bg-sc-bg-1 dark:hover:bg-gray-600"
                                                        action="{{ route('videoevents.destroy', ['model_class_name' => strtolower(class_basename($this->model_fqcn)), 'id' => $row->id]) }}"
                                                        method="POST" class="block"
                                                        onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 text-red-500 dark:text-red-400 ">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (stripos($this->model_fqcn, 'VideoPlaylist') !== false)
                                                    <form class="hover:bg-sc-bg-1 dark:hover:bg-gray-600"
                                                        action="{{ route('video_playlist.edit', ['id' => $row->id]) }}"
                                                        method="GET" class="block">
                                                        @csrf
                                                        <button type="submit"
                                                            class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                            Edit
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (stripos($this->model_fqcn, 'VideoPlaylist') !== false)
                                                    <form class="hover:bg-sc-bg-1 dark:hover:bg-gray-600"
                                                        action="{{ route('video_playlist.destroy', ['model_class_name' => strtolower(class_basename($this->model_fqcn)), 'id' => $row->id]) }}"
                                                        method="POST" class="block"
                                                        onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 text-red-500 dark:text-red-400 ">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (stripos($this->model_fqcn, 'ProductContentResource') !== false)
                                                    <form class="hover:bg-sc-bg-1 dark:hover:bg-gray-600" action="{{ route('resource.edit', ['id' => $row->id]) }}" method="GET" class="block">
                                                        @csrf
                                                        <button type="submit" class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                            Edit
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (stripos($this->model_fqcn, 'ProductContentResource') !== false)
                                                <form class="hover:bg-sc-bg-1 dark:hover:bg-gray-600" action="{{ route('resource.destroy', ['model_class_name' => strtolower(class_basename($this->model_fqcn)), 'id' => $row->id]) }}" method="POST" class="block" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 text-red-500 dark:text-red-400 ">
                                                        Delete
                                                    </button>
                                                </form>
                                                @endif
            
                                                @if (stripos($this->model_fqcn, 'account') !== false)
                                                    <a wire:click.prevent="$dispatchTo('list-context-action', 'show-action', { id: {{ $row->id }}, action: 'disable' })"
                                                        data-modal-target="context-action"
                                                        data-modal-show="context-action" href="#"
                                                        class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                        Disable</a>
                                                    <a wire:click.prevent="$dispatchTo('list-context-action', 'show-action', { id: {{ $row->id }}, action: 'enable' })"
                                                        data-modal-target="context-action"
                                                        data-modal-show="context-action" href="#"
                                                        class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                        Enable</a>
                                                    <a wire:click.prevent="$dispatchTo('list-context-action', 'show-action', { id: {{ $row->id }}, action: 'move_to_delta' })"
                                                        data-modal-target="context-action"
                                                        data-modal-show="context-action" href="#"
                                                        class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                        Move to Delta</a>
                                                    <a wire:click.prevent="$dispatchTo('list-context-action', 'show-action', { id: {{ $row->id }}, action: 'compliment_delta' })"
                                                        data-modal-target="context-action"
                                                        data-modal-show="context-action" href="#"
                                                        class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                        Complimentary access to Delta</a>
                                                    <a wire:click.prevent="$dispatchTo('list-context-action', 'show-action', { id: {{ $row->id }}, action: 'compliment_precall' })"
                                                        data-modal-target="context-action"
                                                        data-modal-show="context-action" href="#"
                                                        class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                        Complimentary access to Precall</a>
                                                    <a wire:click.prevent="$dispatchTo('list-context-action', 'show-action', { id: {{ $row->id }}, action: 'able_sub_delta' })"
                                                        data-modal-target="context-action"
                                                        data-modal-show="context-action" href="#"
                                                        class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                        Ability to subscribe to Delta</a>
                                                    <a wire:click.prevent="$dispatchTo('list-context-action', 'show-action', { id: {{ $row->id }}, action: 'clear_override' })"
                                                        data-modal-target="context-action"
                                                        data-modal-show="context-action" href="#"
                                                        class="block py-2 px-4 hover:bg-sc-bg-1 dark:hover:bg-gray-600 dark:hover:text-white">
                                                        Clear mannual overrides</a>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </td>

                                @foreach ($table_headers as $field)
                                    @php
                                        $model_class = class_basename($this->model_fqcn);
                                        $field_value = $row->$field;
                                    @endphp

                                    @if ($model_class === 'User' && $field === 'Email Verified')
                                        <td class="px-6 py-3 text-start">
                                            @if (!is_null($row->email_verified_at))
                                                <i class="fas fa-check text-green-500"></i>
                                                <!-- Green tick icon for verified emails -->
                                            @else
                                                <i class="fas fa-times text-red-500"></i>
                                                <!-- Red cross icon for unverified emails -->
                                            @endif
                                        </td>
                                    @elseif (stripos($this->model_fqcn, 'Video') !== false && $field === 'description')
                                        <td class="px-4 py-3">{!! $row->$field !!} </td>
                                    @elseif (stripos($this->model_fqcn, 'ProductContentResource') !== false && $field === 'resource_location')
                                        <td class="px-4 py-3">
                                            @if ($row->type === 'image')
                                                <a href="{{ $row->$field }}" target="_blank" class="text-blue-1000 underline">
                                                    {{ $row->$field }}
                                                </a>
                                            @elseif ($row->type === 'video')
                                                <a href="javascript:void(0);" onclick="openModal('https://customer-rsp77k1og7ulg0vt.cloudflarestream.com/{{ $row->$field }}/iframe')" class="text-blue-1000 underline">
                                                    View Video
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $row->$field) }}" target="_blank" class="text-blue-1000 underline">
                                                    {{ $row->$field }}
                                                </a>
                                            @endif
                                        </td>
                                    @elseif (stripos($this->model_fqcn, 'ProductContentResource') !== false && $field === 'coverimage_path')
                                        <td class="px-4 py-3">
                                            @if(!empty($row->$field))
                                                <a href="{{ $row->$field }}" target="_blank">
                                                    <img src="{{ $row->$field }}" alt="Thumbnail" style="width: 100px; height: 100px;" class="rounded-md">
                                                </a>
                                            @else
                                                <span>No Image Available</span>
                                            @endif
                                        </td>

                                    @elseif (stripos($this->model_fqcn, 'ProductContentResource') !== false && $field === 'audience')
                                        <td class="px-4 py-3">{{ json_decode($row->$field, true)[0] ?? '' }}</td>
                                    @elseif (stripos($this->model_fqcn, 'ProductContentResource') !== false && $field === 'resource_list')
                                        <td class="px-4 py-3">{{ $row->resourcelist_name }}</td>
                                    @elseif (stripos($this->model_fqcn, 'VideoPlaylist') !== false && $field === 'video_count')
                                        <td class="px-4 py-3">{{ $row->videos_count }}</td>
                                    @elseif (stripos($this->model_fqcn, 'VideoPlaylist') !== false && $field === 'coverimage_path')
                                        <td class="px-4 py-3">
                                            @if(!empty($row->$field))
                                                <a href="{{ $row->$field }}" target="_blank">
                                                    <img src="{{ $row->$field }}" alt="Thumbnail" style="width: 100px; height: 100px;" class="rounded-md">
                                                </a>
                                            @else
                                                <span>No Image Available</span>
                                            @endif
                                        </td>
                                    @elseif (stripos($this->model_fqcn, 'Video') !== false && $field === 'coverimage_path')
                                    <td class="px-4 py-3">
                                            @if(!empty($row->$field))
                                                <a href="{{ $row->$field }}" target="_blank">
                                                    <img src="{{ $row->$field }}" alt="Thumbnail" style="width: 100px; height: 100px;" class="rounded-md">
                                                </a>
                                            @else
                                                <span>No Image Available</span>
                                            @endif
                                    </td>
                                    @elseif (stripos($this->model_fqcn, 'Video') !== false && $field === 'tags')
                                        @php
                                            $tags = json_decode($row->$field, true); // Decode JSON string to array
                                        @endphp
                                        <td class="px-4 py-3">
                                            @if (is_array($tags))
                                                @foreach ($tags as $tag)
                                                    <span class="tag">{{ $tag }}</span>
                                                    @if (!$loop->last)
                                                        <span>,</span>
                                                    @endif
                                                @endforeach
                                            @else
                                                {{ $tags ?? '' }}
                                            @endif
                                        </td>
                                    @elseif (stripos($this->model_fqcn, 'Video') !== false && $field === 'audience')
                                        <?php 
                                        $audienceArray = json_decode($row->$field, true);
                                        $audienceValue = '';
                                    
                                        // Check if it's an array and has elements
                                        if (is_array($audienceArray) && !empty($audienceArray)) {
                                            $audienceValue = $audienceArray[0]; // Get the first element
                                        }
                                    
                                        // Ensure audienceValue is a string
                                        if (!is_string($audienceValue)) {
                                            $audienceValue = ''; // Set to an empty string if it's not
                                        }
                                        ?>
                                        <td class="px-4 py-3">{{ htmlspecialchars($audienceValue) }}</td>
                                    
                                    @elseif (stripos($this->model_fqcn, 'Video') !== false && $field === 'playlist')
                                        <td class="px-4 py-3">{{ $row->playlist_name }}</td>
                                    @elseif (stripos($this->model_fqcn, 'VideoEvent') !== false && $field === 'message')
                                        <td class="px-4 py-3">{{ strip_tags($row->message) }}</td>
                                    @else
                                        <td class="px-4 py-3">
                                            @if (stripos($this->model_fqcn, 'User') !== false && ($field === 'name' || $field === 'email'))
                                                <a href="{{ route('admin.user.profile', $row->id) }}"
                                                    class="text-blue-1000 underline" target="_blank">
                                                    {{ $row->$field }}
                                                </a>
                                            @else
                                                {{ $row->$field ?? '' }}
                                            @endif
                                        </td>
                                    @endif
                                @endforeach

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-5">
                {{ $this->getList()->links() }}
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="videoModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75 flex items-center justify-center">
        <div class="bg-white p-4 rounded-lg shadow-lg max-w-2xl w-full">
            <button onclick="closeModal()" class="float-right text-black">X</button>
            <div class="mt-2">
                <iframe id="videoFrame" src="" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" allowfullscreen class="w-full h-96"></iframe>
            </div>
        </div>
    </div>
    <script>
        function openModal(videoUrl) {
            document.getElementById('videoFrame').src = videoUrl;
            document.getElementById('videoModal').classList.remove('hidden');
        }
    
        function closeModal() {
            document.getElementById('videoFrame').src = '';
            document.getElementById('videoModal').classList.add('hidden');
        }
    </script>
    
    <script>
        document.querySelectorAll('form[action*="/admin/crud/list/User"]').forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
</section>
