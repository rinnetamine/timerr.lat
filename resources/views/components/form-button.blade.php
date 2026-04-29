@props(['disabled' => false])

<button {{ $attributes->merge(['class' => 'rounded-md px-4 py-2 text-sm font-medium text-gray-300 border border-gray-700 transition-all duration-300 ' . ($disabled ? 'opacity-50 cursor-not-allowed' : 'hover:text-neon-accent hover:bg-gray-800/80'), 'type' => 'submit', 'disabled' => $disabled]) }}>
    {{ $slot }}
</button>
