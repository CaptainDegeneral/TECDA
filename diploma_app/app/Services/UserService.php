<?php

namespace App\Services;

use App\Http\Requests\User\EditUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserService
{
    /**
     * @param StoreUserRequest $request
     * @return void
     */
    public static function create(StoreUserRequest $request): void
    {
        $user = User::create([
            'last_name' => $request->last_name,
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        $user->markEmailAsVerified();
    }

    /**
     * @param EditUserRequest $request
     * @return bool
     * @throws Throwable
     */
    public static function edit(EditUserRequest $request): bool
    {
        return DB::transaction(function () use ($request) {
            $user = User::findOrFail($request->id);

            $data = $request->only([
                'last_name',
                'name',
                'surname',
                'email',
                'role_id',
            ]);

            if ($request->verified && $user->email_verified_at === null) {
                $user->markEmailAsVerified();
            } else if (!$request->verified && $user->email_verified_at !== null) {
                $data['email_verified_at'] = null;
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            return $user->update($data);
        });
    }

    /**
     * @param int $userId
     * @return bool|null
     */
    public static function delete(int $userId): ?bool
    {
        return User::findOrFail($userId)->delete();
    }
}
