<?php

namespace Ghijk\DonationCheckout\Services;

use Illuminate\Support\Str;
use Statamic\Facades\User;

class UserService
{
    public function findByEmail(string $email)
    {
        ray('find by email '.$email);

        return User::findByEmail($email);
    }

    public function createUser(
        string $firstName,
        string $lastName,
        string $email
    ) {
        ray('create user '.$email);
        $user = User::make()->email($email);
        $user->password(Str::random(16))
            ->data([
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
        $user->save();
        ray($user);

        return $user;
    }

    public function updateUser($user, $data)
    {
        ray('update user '.$user->email());
        $user->data($data);
        $user->save();
        ray($user);

        return $user;
    }
}
