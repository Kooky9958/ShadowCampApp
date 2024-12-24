<select id="resource_list" name="resource_list" required class="border-gray-300 focus:border-scdefault-300 focus:ring focus:ring-scdefault-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full">
    @php
        foreach(\App\Http\Controllers\ProductContentResourceListController::getAllResourceLists() as $resource_list) {
            echo '<option value="'.$resource_list->url_id.'">'.$resource_list->name.'</option>';
        }
    @endphp
</select>