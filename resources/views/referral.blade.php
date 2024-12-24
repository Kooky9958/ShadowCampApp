<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Referrals') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

            @if ($referral_code !== null)
                <p class="mt-1 text-sm text-gray-600 p-2">
                    Below is a referral code. Give this to a friend so they can get great value on their next Shadow Camp subscription.
                </p>

                <div class="text-xl text-gray-900 p-2"">
                    {{ $referral_code }}
                </div>
            @else
                <p class="mt-1 text-sm text-gray-600 p-2">
                    There are no referral codes available for this account at this time.
                </p>
            @endif
            

        </div>
</x-app-layout>
