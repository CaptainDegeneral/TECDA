<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    /**
     * @param string|null $searchValue
     * @return LengthAwarePaginator
     */
    public static function all(?string $searchValue): LengthAwarePaginator
    {
        $users = User::with(['role:id,name'])->select([
            'id',
            'last_name',
            'name',
            'surname',
            'email',
            'email_verified_at',
            'role_id',
        ]);

        if ($searchValue) {
            $users->where(function ($query) use ($searchValue) {
                $query->where('last_name', 'like', "%$searchValue%")
                    ->orWhere('name', 'like', "%$searchValue%")
                    ->orWhere('surname', 'like', "%$searchValue%")
                    ->orWhere('email', 'like', "%$searchValue%")
                    ->orWhereHas('role', function ($roleQuery) use ($searchValue) {
                        $roleQuery->whereLike('name', "%$searchValue%");
                    });
            });
        }

        return $users->paginate(10);
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
