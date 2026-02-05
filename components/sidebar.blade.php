<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {

  #[Computed]
  public function user()
  {
    return Auth::user();
  }

  public function logout()
  {
    Auth::logout();
    return $this->redirectRoute("login");
  }
};
?>


<div class="contents">
  <flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.header class="hidden lg:flex">
      <flux:spacer />
      <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" tooltip="Expand/Collapse Sidebar" />
    </flux:sidebar.header>
    <flux:sidebar.nav>
      <x-sidebar-item icon="home" href="{{ route('home') }}">Home</x-sidebar-item>
      <flux:sidebar.group expandable icon="star" heading="Favorites" class="grid">
        <x-sidebar-item href="#">Item 1</x-sidebar-item>
        <x-sidebar-item href="#">Item 2</x-sidebar-item>
      </flux:sidebar.group>
      <x-sidebar-item href="{{ route('members') }}" icon="user-group">Members</x-sidebar-item>
    </flux:sidebar.nav>
    <flux:sidebar.spacer />
    <flux:dropdown position="top" align="start" class="max-lg:hidden">
      <flux:sidebar.profile avatar="{{ $this->user->picture() }}" name="{{ $this->user->name }}" icon:trailing="chevron-up" />
      <x-user-menu />
    </flux:dropdown>
  </flux:sidebar>
  <flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />
    <flux:spacer />
    <flux:dropdown position="top" align="start">
      <flux:profile name="{{ $this->user->name }}" avatar="{{ $this->user->picture() }}" />
      <x-user-menu />
    </flux:dropdown>
  </flux:header>
</div>
