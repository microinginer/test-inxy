<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);

        return $user;
    }
}
