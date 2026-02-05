<?php

namespace App\Models;

use App\Mail\EmailVerification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function hasAlreadySentAVerificationEmail()
  {
    $emailInPreviousMinute = DB::table('password_reset_tokens')
      ->where('email', $this->email)
      ->where('created_at', '>', now()->subMinutes(2))
      ->first();

    if ($emailInPreviousMinute) {
      return $emailInPreviousMinute->token;
    }
  }

  public function sendEmailVerification()
  {

    $activeToken = $this->hasAlreadySentAVerificationEmail();
    if ($activeToken != null) {
      return $activeToken;
    }

    DB::table('password_reset_tokens')
      ->where('email', $this->email)
      ->orWhere('created_at', '>', now()->subMinutes(15))
      ->delete();

    $generatedCode = rand(100000, 999999);

    DB::table('password_reset_tokens')->insert([
      'email' => $this->email,
      'token' => $generatedCode,
      'created_at' => now(),
    ]);

    $mail = new EmailVerification($generatedCode);

    Mail::to($this->email)->send($mail);

    return $generatedCode;
  }

  public function picture()
  {
    if (!$this->profile_picture) {
      return null;
    }

    return Storage::url($this->profile_picture);
  }
}
