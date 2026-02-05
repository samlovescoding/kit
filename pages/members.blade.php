<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {

  use WithPagination;

  #[Computed]
  public function users()
  {
    return User::paginate(20);
  }

  public function approve(User $user)
  {
    $user->email_verified_at = now();
    $user->save();
  }

  public function delete(User $user)
  {
    if ($user->id === 1) {
      return;
    }
    $user->delete();
  }
};
?>


<div>
  <title>Members</title>
  <flux:heading size="xl" level="1">Members</flux:heading>
  <flux:text class="mt-2 mb-6 text-base">Here's all your members</flux:text>
  <flux:separator variant="subtle" />

  <div class="flex flex-col">
    @foreach($this->users as $user)
    <div class="flex justify-between hover:bg-zinc-50 dark:hover:bg-zinc-900 p-4">
      <div class="flex">
        <div class="mr-4">{{ $user->name }}</div>

        <flux:badge>{{ $user->email }}</flux:badge>
      </div>

      <div class="flex">
        @if($user->id != auth()->id() && !isset($user->email_verified_at))
        <flux:button size="xs" wire:click="approve({{ $user->id }})">Approve</flux:button>
        @endif

        @if($user->id != 1)
        <flux:button size="xs" wire:click="delete({{ $user->id }})">Delete</flux:button>
        @else
        <flux:tooltip content="Cannot delete an admin account.">
          <flux:button class="cursor-not-allowed opacity-30" size="xs">Delete</flux:button>
        </flux:tooltip>
        @endif
      </div>
    </div>
    @endforeach
  </div>

  <flux:pagination :paginator="$this->users" />
</div>
