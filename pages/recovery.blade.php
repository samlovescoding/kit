<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

new #[Layout("layouts::authentication")] class extends Component {

  #[Validate("required|email")]
  public string $email = "himself@samlovescoding.com";

  #[Validate("required|string|size:6")]
  public string $code;

  #[Validate("required|string|confirmed")]
  public string $password;
  public string $password_confirmation;

  public bool $isCodeSent = false;

  public function sendEmail()
  {
    $fields = $this->validate([
      "email" => "required|email"
    ]);

    $user = User::where('email', $fields["email"])->first();

    if (!$user) {
      return $this->addError("email", "Email is not registered to any account.");
    }

    $user->sendEmailVerification();

    $this->isCodeSent = true;
  }

  public function resendEmail()
  {
    $fields = $this->validate([
      "email" => "required|email"
    ]);

    $user = User::where('email', $fields["email"])->first();

    $user->sendEmailVerification();
  }

  public function submit()
  {
    $fields = $this->validate();

    $validationMessage = "Code is expired or incorrect.";

    $token = DB::table('password_reset_tokens')
      ->where('email', $fields["email"])
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

    $user = User::where('email', $fields["email"])->first();

    $user->password = $fields["password"];

    $user->save();

    return $this->redirectRoute("login");
  }
};
?>

<div class="w-80 max-w-80 space-y-6">
  <title>Account Recovery</title>
  <flux:heading class="text-center" size="xl">
    Let's recover your account
  </flux:heading>


  @if($isCodeSent)
  <form method="POST" wire:submit="submit" class="flex flex-col gap-6">
    <flux:input disabled label="Email" type="email" :value="$this->email" />

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

    <flux:input wire:model="password" label="New Password" type="password" placeholder="Choose your new password" />

    <flux:input wire:model="password_confirmation" label="Confirm Password" type="password" placeholder="Re-enter your password" />


    <flux:button type="submit" variant="primary" class="w-full">Change Password</flux:button>
  </form>
  @else
  <form method="POST" wire:submit="sendEmail" class="flex flex-col gap-6">
    <flux:input wire:model="email" label="Email" type="email" placeholder="ryanreynolds@gmail.com" />

    <flux:button type="submit" variant="primary" class="w-full">Send 6 digit code</flux:button>
  </form>
  @endif

  <flux:subheading class="text-center">
    Dont have an account? <flux:link wire:navigate href="{{ route('register') }}">Sign up for free</flux:link>
  </flux:subheading>
</div>
