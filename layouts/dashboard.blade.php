<x-html class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
  <livewire:sidebar />

  <flux:main>
    {{ $slot }}
  </flux:main>
</x-html>
