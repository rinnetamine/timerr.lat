{{-- Šis komponents rāda validācijas kļūdu konkrētam formas laukam. --}}
@props(['name'])

@error($name)
    <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
@enderror
