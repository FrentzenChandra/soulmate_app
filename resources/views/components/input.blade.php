@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border border-gray-200 focus:border-[#5E17EB] focus:ring-2 focus:ring-purple-100 rounded-xl shadow-xs text-sm font-semibold text-gray-900 transition-all outline-none disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed']) !!}>
