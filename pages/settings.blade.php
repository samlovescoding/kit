<?php

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

new class extends Component
{
  use WithFileUploads;

  public $user;
  public $id;
  public $name;
  public $email;
  public $profilePicture;
  public $profilePictureUrl;
  public $profileSuccess;

  public function mount()
  {
    $this->user = Auth::user();
    $this->id = $this->user->id;
    $this->name = $this->user->name;
    $this->email = $this->user->email;
    $this->profilePictureUrl = $this->user->picture();
  }

  public function save()
  {
    $this->reset('profileSuccess');
    $this->resetErrorBag();
    $fields = $this->validate([
      'name'           => 'required|string|max:255',
      'email'          => 'required|email|unique:users,email,' . $this->user->id,
      'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
    ]);

    $this->user->update([
      'name'  => $fields['name'],
      'email' => $fields['email'],
    ]);
    $this->profileSuccess = 'Your profile has been updated successfully.';

    if ($this->profilePicture) {
      $this->handleProfilePictureUpload();
    }
  }

  private function handleProfilePictureUpload()
  {
    if ($this->user->profile_picture) {
      Storage::delete($this->user->profile_picture);
    }

    $manager = new ImageManager(new Driver());
    $image = $manager->read($this->profilePicture)
      ->cover(256, 256)
      ->toJpeg(80);

    $filePath = 'avatars/' . Str::uuid()->toString() . '-' . $this->user->id . '.jpg';

    Storage::disk('public')->put(
      $filePath,
      $image
    );

    $this->user->profile_picture = $filePath;
    $this->profilePictureUrl = Storage::url($filePath);

    $this->user->save();
  }

  public $currentPassword;
  public $newPassword;
  public $newPasswordConfirmation;
  public $passwordSuccess;

  public function changePassword()
  {
    $this->resetErrorBag();
    $this->reset('passwordSuccess');
    $fields = $this->validate([
      'currentPassword'         => 'required|string|min:8',
      'newPassword'             => 'required|string|min:8',
      'newPasswordConfirmation' => 'required|string|min:8|same:newPassword',
    ]);

    $user = Auth::user();
    if (!Hash::check($fields['currentPassword'], $user->password)) {
      $this->addError('currentPassword', 'The current password is incorrect.');
    }

    if (!$this->getErrorBag()->isEmpty()) {
      return;
    }

    $user->password = Hash::make($fields['newPassword']);
    $user->save();
    $this->reset('currentPassword', 'newPassword', 'newPasswordConfirmation');
    $this->passwordSuccess = 'Your password has been changed successfully.';
  }

  public function removeProfilePicture()
  {
    // Store the profile picture path before setting it to null
    $oldProfilePicture = $this->user->profile_picture;

    // Delete the file from storage if it exists
    if ($oldProfilePicture) {
      Storage::delete($oldProfilePicture);
    }

    // Update the user record
    $this->user->profile_picture = null;
    $this->user->save();
    $this->profilePictureUrl = null;
  }
};
?>

<div>
  <title>Settings</title>
  <flux:heading size="xl">Customize Your Profile</flux:heading>
  <flux:separator variant="subtle" class="my-8" />
  <x-form class="flex flex-col lg:flex-row gap-4 lg:gap-6" wire:submit="save">
    <div class="w-80">
      <flux:heading size="lg">Profile</flux:heading>
      <flux:subheading>This is how others will see you on the site. Your email is private and never shared with anyone.</flux:subheading>

      <div>
        <div class="mt-6 flex items-center gap-2">
          <flux:avatar size="lg" color="auto"
            name="{{ $this->name }}"
            color:seed="{{ $this->id }}"
            :src="$this->profilePictureUrl" />
          <flux:text size="lg">{{ $this->name }}</flux:text>
        </div>
      </div>
    </div>
    <div class="flex-1 space-y-6">
      <flux:input wire:model="name" label="Your Name" />
      <flux:input wire:model="profilePicture" label="Profile Picture" type="file" />
      @if($profileSuccess)
      <div>
        <flux:text x-data="{ show: true }" x-init="setTimeout(() => show = false, 10000)">{{ $profileSuccess }}</flux:text>
      </div>
      @endif
      <div class="flex justify-end items-center gap-4">
        <flux:button type="submit" variant="primary">Update profile</flux:button>
      </div>
    </div>
  </x-form>
  <flux:separator variant="subtle" class="my-8" />
  <x-form class="flex flex-col lg:flex-row gap-4 lg:gap-6" wire:submit="changePassword">
    <div class="w-80">
      <flux:heading size="lg">Security</flux:heading>
      <flux:subheading>Manage your account security.</flux:subheading>
    </div>
    <div class="flex-1 space-y-6">
      <flux:input wire:model="currentPassword" label="Current Password" type="password" viewable />
      <flux:input wire:model="newPassword" label="New Password" type="password" viewable />
      <flux:input wire:model="newPasswordConfirmation" label="Confirm New Password" type="password" viewable />

      <div class="flex justify-end items-center gap-4">
        @if($passwordSuccess)
        <flux:text>{{ $passwordSuccess }}</flux:text>
        @endif
        <flux:button type="submit" variant="primary">Change Password</flux:button>
      </div>
    </div>
  </x-form>
</div>
