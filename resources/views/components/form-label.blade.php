{{-- Šis komponents veido vienotu formas lauka etiķeti. --}}
<label {{ $attributes->merge(['class' => 'block text-sm font-medium leading-6 text-gray-300']) }}>{{ $slot }}</label>
