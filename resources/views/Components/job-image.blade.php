@props(['job', 'ratio' => 'aspect-[16/9]'])

<img
    src="{{ $job->imageUrl() }}"
    alt="{{ $job->title }}"
    {{ $attributes->merge(['class' => $ratio . ' w-full rounded-md border border-gray-700 bg-gray-900/60 object-cover']) }}
>
