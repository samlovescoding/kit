<flux:menu>
  <flux:radio.group class="mb-1" x-data variant="segmented" x-model="$flux.appearance">
    <flux:radio value="light" icon="sun" />
    <flux:radio value="dark" icon="moon" />
    <flux:radio value="system" icon="computer-desktop" />
  </flux:radio.group>

  <flux:menu.item href="{{ route('settings') }}" wire:navigate icon="cog-6-tooth">Settings</flux:menu.item>
  <flux:menu.item wire:click="logout" icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
</flux:menu>
