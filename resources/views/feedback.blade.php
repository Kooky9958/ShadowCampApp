<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    {{ $message }}
                </div>
                <div class="mt-4">
                    <a href="#" onclick="history.back()" class="underline text-sc-orange-5 hover:no-underline"><< Go back</a>
                </div>
            </div>
        </div>
</x-app-layout>