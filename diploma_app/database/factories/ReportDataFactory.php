<?php

namespace Database\Factories;

use Faker\Factory as Faker;

class ReportDataFactory
{
    /**
     * Возвращает тестовые данные для отчета.
     *
     * @param int $disciplineCount Количество дисциплин (по умолчанию 30)
     * @param int $yearCount Количество учебных лет (по умолчанию 5)
     * @return array
     */
    public static function getTestReportData(int $disciplineCount = 30, int $yearCount = 5): array
    {
        $faker = Faker::create('ru_RU');

        $disciplines = [];

        for ($i = 0; $i < $disciplineCount; $i++) {
            $disciplines[] = $faker->unique()->randomElement([
                'Математика', 'Физика', 'Химия', 'Биология', 'История', 'География', 'Литература',
                'Английский', 'Экономика', 'Информатика', 'Философия', 'Искусство', 'Музыка',
                'Физкультура', 'Социология', 'Психология', 'Политология', 'Право', 'Бизнес',
                'Статистика', 'Экология', 'Геология', 'Астрономия', 'Инженерия', 'Медицина',
                'Архитектура', 'Лингвистика', 'Антропология', 'Драматургия',
            ]) ?: $faker->word . ' ' . $i;
        }

        $years = [];
        $startYear = 2018;

        for ($i = 0; $i < $yearCount; $i++) {
            $years[] = sprintf('%d-%d', $startYear + $i, $startYear + $i + 1);
        }

        $overallResults = [];

        foreach ($disciplines as $discipline) {
            $overallResults[] = [
                'discipline' => $discipline,
                'avgQuality' => $faker->randomFloat(1, 30, 95),
                'avgAverageScore' => $faker->randomFloat(1, 2.5, 5.0),
            ];
        }

        $qualityTable = [];

        foreach ($disciplines as $discipline) {
            $row = ['discipline' => $discipline];

            foreach ($years as $year) {
                $row[$year] = $faker->boolean(80) ? $faker->randomFloat(1, 30, 95) : null;
            }

            $qualityTable[] = $row;
        }

        $averageScoreTable = [];

        foreach ($disciplines as $discipline) {
            $row = ['discipline' => $discipline];
            foreach ($years as $year) {
                $row[$year] = $faker->boolean(80) ? $faker->randomFloat(1, 2.5, 5.0) : null;
            }

            $averageScoreTable[] = $row;
        }

        return [
            'data' => [
                'overallResults' => $overallResults,
                'finalResults' => [
                    'qualityTable' => $qualityTable,
                    'averageScoreTable' => $averageScoreTable,
                ],
            ],
            'user' => [
                'last_name' => $faker->lastName,
                'name' => $faker->firstName,
                'surname' => $faker->middleName,
            ],
        ];
    }

    /**
     * Возвращает тестовый заголовок отчета.
     *
     * @return string
     */
    public static function getTestReportTitle(): string
    {
        return 'Test Report from ' . now()->format('d.m.Y H:i');
    }
}
