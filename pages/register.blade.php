<?php

use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

new #[Layout("layouts::authentication")] class extends Component {

  #[Validate("required|string|min:2")]
  public string $name;

  #[Validate("required|string|email|unique:users,email")]
  public string $email;

  #[Validate("required|string|min:8|confirmed")]
  public string $password;

  public string $password_confirmation;

  public function submit()
  {
    $fields = $this->validate();

    $user = User::create($fields);

    $user->sendEmailVerification();

    Session::put("verification_required_for_user", $user->id);

    return $this->redirectRoute("verification");
  }
};
?>

<div class="w-80 max-w-80 space-y-6">
  <title>Registration</title>
  <flux:heading class="text-center" size="xl">Create an account</flux:heading>


  <form method="POST" wire:submit.prevent="submit" class="flex flex-col gap-6">
    <flux:input wire:model="name" label="Name" type="text" placeholder="Ryan Reynolds" />

    <flux:input wire:model="email" label="Email" type="email" placeholder="ryanreynolds@gmail.com" />

    <flux:field>
      <div class="mb-3 flex justify-between">
        <flux:label>Password</flux:label>

        <flux:link href="{{ route('recovery') }}" variant="subtle" class="text-sm">
          Forgot password?
        </flux:link>
      </div>

      <flux:input wire:model="password" type="password" placeholder="Your password" />

      <flux:error name="password" />
    </flux:field>

    <flux:input wire:model="password_confirmation" label="Confirm Password" type="password" placeholder="Re-enter your password" />

    <flux:button type="submit" variant="primary" class="w-full">Create Account</flux:button>
  </form>

  <flux:subheading class="text-center">
    Already have an account? <flux:link wire:navigate href="{{ route('login') }}">Login instead</flux:link>
  </flux:subheading>
</div>
