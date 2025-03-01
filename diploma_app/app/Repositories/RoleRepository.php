<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    /**
     * @return Collection
     */
    public static function all(): Collection
    {
        return Role::get(['id', 'name']);
    }
}
