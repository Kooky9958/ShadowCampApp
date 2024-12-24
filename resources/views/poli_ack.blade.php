<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200 w-1/2">

                <h3 class="font-semibold text-xl pb-4">Pay with POLi</h3>
                @if ($mode == 'success')
                <p>Your payment was successful.</p>
                @elseif ($mode == 'fail')
                <p>Your payment has FAILED.</p>
                @elseif ($mode == 'cancel')
                <p>It appears you chose to cancel your payment.</p>
                @endif

            </div> 
        </div>
    </div>
</x-app-layout>