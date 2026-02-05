@props([
  'href',
  'matching' => 'exact', // exact, start, end, contains
  'matchWith' => 'url', // url, path, route
  'matchRef' => null,
  'icon' => null,
])

@php
  $current = false;

  $matchWith = match ($matchWith) {
    'url' => url()->current(),
    'path' => request()->path(),
    'route' => request()->route()->getName()
  };

  $reference = $matchRef ?? $href;

  $current = match ($matching) {
    'exact' => $matchWith === $reference,
    'start' => str_starts_with($matchWith, $reference),
    'end' => str_ends_with($matchWith, $reference),
    'contains' => str_contains($matchWith, $reference),
  };

@endphp

<flux:sidebar.item wire:navigate :$current :$href :$icon {{ $attributes }}>
    {{ $slot }}
</flux:sidebar.item>
