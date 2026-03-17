<?php

namespace Ghijk\DonationCheckout\Services;

use Statamic\Facades\User;
use Illuminate\Support\Str;

class UserService
{
    public function findByEmail(string $email): mixed
    {
        return User::findByEmail($email);
    }

    public function createUser(
        string $firstName,
        string $lastName,
        string $email
    ): mixed {
        $user = User::make()->email($email);
        $user->password(Str::random(16))
            ->data([
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
        $user->save();

        return $user;
    }

    public function updateUser($user, array $data): mixed
    {
        $user->merge($data);
        $user->save();

        return $user;
    }
}
