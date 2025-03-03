<?php

namespace App\Services;

use App\Models\Subject;

class SubjectService
{
    /**
     * @param string|null $name
     * @param string|null $code
     * @return Subject
     */
    public static function create(?string $name, ?string $code): Subject
    {
        return Subject::create([
            'name' => $name,
            'code' => $code,
        ]);
    }

    /**
     * @param int $id
     * @param string|null $name
     * @param string|null $code
     * @return bool
     */
    public static function update(int $id, ?string $name, ?string $code): bool
    {
        return Subject::where('id', $id)->update([
            'name' => $name,
            'code' => $code,
        ]);
    }

    /**
     * @param int $id
     * @return bool|null
     */
    public static function delete(int $id): ?bool
    {
        return Subject::where('id', $id)->delete();
    }
}
