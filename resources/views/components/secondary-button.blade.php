<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-200 rounded-xl font-bold text-xs text-gray-700 uppercase tracking-wider shadow-xs hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-200 focus:ring-offset-2 disabled:opacity-40 transition-all cursor-pointer']) }}>
    {{ $slot }}
</button>
