<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('profile.delete-user-form')
                </div>
            @endif

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

            <!-- Display Upcoming Payment -->
            <x-section-border />
            <div class="mt-10 sm:mt-0">
                <h4 class="font-semibold text-lg">Upcoming Payment</h4>
                <p class="mt-2">
                    @if($mr_sub_start_date !== '')
                    Your next payment is due by <span class="text-green-600"> {{ date('d M Y', strtotime(date('Y-m-d', $mr_sub_start_date).' +9 weeks')) }} </span>.
                    @else
                        No upcoming payments scheduled.
                    @endif
                </p>
            </div>    
        </div>
    </div>
</x-app-layout>
