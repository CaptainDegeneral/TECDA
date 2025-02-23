<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /**
     * @return Collection
     */
    public static function all(): Collection
    {
        return User::with(['role:id,name'])->get([
            'id',
            'last_name',
            'name',
            'surname',
            'email',
            'email_verified_at',
            'role_id',
        ]);
    }

    /**
     * @param int $userId
     * @return User
     */
    public static function get(int $userId): User
    {
        return User::with(['role:id,name'])->select([
            'id',
            'last_name',
            'name',
            'surname',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'role_id',
        ])->find($userId);
    }
}
