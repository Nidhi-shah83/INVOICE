<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(array $credentials, bool $remember = false): bool
    {
        return Auth::attempt($credentials, $remember);
    }

    public function register(array $attributes): User
    {
        return User::create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => Hash::make($attributes['password']),
        ]);
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
