@php
    $query = $report_out['db_query'];
    $query_row_head = array_keys((array) $query[0]);
    $query_row_raw_html_fields = (array_key_exists('raw_html_fields', $report_out)) ? $report_out['raw_html_fields'] : [];
@endphp
<section class="dark:bg-gray-900 p-3 sm:p-5">
    <div class="mx-auto max-w-screen-2xl px-4 lg:px-12">
        <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
            <div class="p-5">
                {{ $query->links() }}
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            @foreach ($query_row_head as $var)
                                <th scope="col" class="px-4 py-3">{{ $var }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($query as $item)
                            @php
                                $item_array = (array) $item;
                            @endphp
                            <tr class="border-b dark:border-gray-700">
                                @foreach ($query_row_head as $field)
                                    @if (in_array($field, $query_row_raw_html_fields))
                                        <td class="px-4 py-3">{!! $item_array[$field] !!}</td>                                        
                                    @else
                                        <td class="px-4 py-3">{{ $item_array[$field] }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>