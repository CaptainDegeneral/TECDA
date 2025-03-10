<?php

namespace App\Repositories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SubjectRepository
{
    /**
     * Получение всех дисциплин
     *
     * @param string|null $subjectProperty
     * @return LengthAwarePaginator
     */
    public static function all(?string $subjectProperty = null): LengthAwarePaginator
    {
        $subject = Subject::select(['id', 'code', 'name']);

        if ($subjectProperty) {
            $subject->whereLike('name', "%$subjectProperty%")
                ->orWhereLike('code', "%$subjectProperty%");
        }

        return $subject->orderBy('name')->paginate(10);
    }

    /**
     * @return Collection
     */
    public static function getAllWithoutPagination(): Collection
    {
        $subjects = Subject::get([
            'id',
            'code',
            'name',
        ]);

        $subjects = $subjects->map(function ($subject) {
            $parts = array_filter([$subject->code, $subject->name], fn($value) => !empty($value));
            $subject->code_name = $parts ? implode(' ', $parts) : null;
            return $subject;
        });

        $sorted = $subjects->sortBy('code_name');

        return $sorted->values();
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
