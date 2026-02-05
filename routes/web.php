<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', "pages::index")->name("index");

Route::middleware('layout:auth')->group(function () {
  Route::livewire('/login', "pages::login")->name("login");
  Route::livewire('/register', "pages::register")->name("register");
  Route::livewire('/recovery', "pages::recovery")->name("recovery");
  Route::livewire('/verify', "pages::verification")->name("verification");
});


Route::middleware("auth")->middleware('layout:dashboard')->group(function () {
  Route::livewire('/home', "pages::home")->name("home");
  Route::livewire('/members', "pages::members")->name("members");
  Route::livewire('/settings', "pages::settings")->name("settings");
});
