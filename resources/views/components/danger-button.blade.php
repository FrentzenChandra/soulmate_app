<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-rose-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-wider shadow-sm hover:bg-rose-700 active:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-200 focus:ring-offset-2 disabled:opacity-40 transition-all cursor-pointer']) }}>
    {{ $slot }}
</button>
