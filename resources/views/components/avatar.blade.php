@props(['user', 'size' => 'md'])

@php
    $sizes = [
        'xs' => 'h-7 w-7 text-xs',
        'nav' => 'h-8 w-8 text-xs',
        'sm' => 'h-10 w-10 text-sm',
        'md' => 'h-12 w-12 text-base',
        'lg' => 'h-24 w-24 text-3xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($user->avatarUrl())
    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->first_name }} {{ $user->last_name }}" onerror="this.onerror=null;this.src='{{ $user->defaultAvatarUrl() }}';" {{ $attributes->merge(['class' => $sizeClass . ' rounded-full object-cover border border-neon-accent/30 bg-gray-900/70']) }}>
@else
    <div {{ $attributes->merge(['class' => $sizeClass . ' rounded-full border border-neon-accent/30 bg-gray-900/70 flex items-center justify-center font-semibold text-neon-accent']) }}>
        {{ $user->initials() }}
    </div>
@endif
