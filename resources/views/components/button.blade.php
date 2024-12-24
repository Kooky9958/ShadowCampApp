<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-sc-orange-9 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sc-orange-5 active:bg-sc-orange-9 focus:outline-none focus:border-sc-orange-9 focus:ring focus:ring-sc-orange-1 disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
