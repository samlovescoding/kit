<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

new #[Layout("layouts::authentication")] class extends Component {

  #[Validate("required|string|size:6")]
  public string $code;

  public ?User $user;

  public function mount()
  {
    $registeredUserId = Session::get('verification_required_for_user');
    $this->user = User::find($registeredUserId);
    if (!$this->user) {
      dd($this->user);
    }
  }

  public function resendEmail()
  {
    $this->user->sendEmailVerification();
  }

  public function submit()
  {
    $fields = $this->validate();

    $validationMessage = "Code is expired or incorrect.";

    $token = DB::table('password_reset_tokens')
      ->where('email', $this->user->email)
      ->where('created_at', '>', now()->subMinutes(15))
      ->latest()
      ->first();

    if (!$token) {
      return $this->addError("code", $validationMessage);
    }

    $isTokenValid = $token->token === $fields["code"];

    if (!$isTokenValid) {
      return $this->addError("code", $validationMessage);
    }

    $this->user->email_verified_at = now();

    $this->user->save();

    Auth::login($this->user);

    return $this->redirectRoute("home");
  }
};
?>

<div class="w-80 max-w-80 space-y-6">
  <title>Email Verification</title>
  <flux:heading class="text-center" size="xl">
    Please check your Inbox
  </flux:heading>

  <flux:callout>
    <flux:callout.heading>Verification is required</flux:callout.heading>
    <flux:callout.text>
      An email containing a 6 digit verification code
      has been sent to claim ownership of this email.
    </flux:callout.text>
    <flux:callout.text>
      {{ $user->email }}
    </flux:callout.text>
  </flux:callout>


  <form method="POST" wire:submit="submit" class="flex flex-col gap-6">
    <flux:field>
      <div class="mb-3 flex justify-between">
        <flux:label>OTP Code</flux:label>

        <flux:tooltip content="Resend will send a brand new code via email and expire all previous codes.">
          <flux:link wire:click.prevent="resendEmail" variant="subtle" class="text-sm cursor-pointer">
            Resend
          </flux:link>
        </flux:tooltip>
      </div>

      <flux:input wire:model="code" placeholder="6 Digit Unique Code" />

      <flux:error name="code" />
    </flux:field>


    <flux:button type="submit" variant="primary" class="w-full">Verify Email Account</flux:button>
  </form>
</div>
