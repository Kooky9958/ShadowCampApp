<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

            <h3 class="text-lg font-medium text-gray-900 p-2">Transactions</h3>

            <p class="mt-1 text-sm text-gray-600 p-2">
                The most recent successful transactions on your account.
            </p>

            <div class="relative overflow-x-auto p-2">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Description
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Amount
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Currency
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Invoice
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaction)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $transaction->id }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ $transaction->date }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $transaction->description }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $transaction->amount }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ strtoupper($transaction->currency) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


        </div>
</x-app-layout>
