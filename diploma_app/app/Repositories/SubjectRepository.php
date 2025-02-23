<?php

namespace App\Repositories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

class SubjectRepository
{
    /**
     * Получение всех дисциплин
     *
     * @return Collection
     */
    public static function all(): Collection
    {
        return Subject::get(['code', 'name']);
    }

    /**
     * @param int $id
     * @return Subject|null
     */
    public static function get(int $id): ?Subject
    {
        return Subject::get(['id', 'code', 'name'])->where('id', $id)->first();
    }
}
