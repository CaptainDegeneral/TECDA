<?php

use PhpOffice\PhpWord\SimpleType\Jc;

return [
    'styles' => [
        'default' => [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'fontName' => 'Times New Roman',
            'fontSize' => 11,
            'numberingStyleName' => 'multilevel',

        ],
        'title' => [
            'bold' => true,
            'size' => 14,
            'name' => 'Times New Roman'
        ],
        'subtitle' => [
            'size' => 12,
            'name' => 'Times New Roman'
        ],
        'table' => [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'width' => 9355.5,
            'unit' => 'dxa',
        ],
        'header' => [
            'bold' => true,
            'valign' => 'center',
            'align' => 'center',
            'size' => 11,
        ],
        'cell' => [
            'valign' => 'center',
            'size' => 11,
        ],
        'description' => [
            'font' => [
                'size' => 11,
                'name' => 'Times New Roman'
            ],
            'paragraph' => [
                'alignment' => Jc::BOTH,
                'indentation' => [
                    'firstLine' => 425.25
                ],
                'tabs' => [
                    [
                        'type' => 'left',
                        'position' => 850.
                    ]
                ],
            ],
        ],
        'averageScoreText' => [
            'font' => [
                'size' => 11,
                'name' => 'Times New Roman',
                'lineHeight' => 1.0,
                'spaceBefore' => 0,
                'spaceAfter' => 0
            ],
            'paragraph' => [
                'indentation' => [
                    'firstLine' => 425.25
                ],
                'alignment' => Jc::BOTH,
            ],
        ],
        'qualityText' => [
            'font' => [
                'size' => 11,
                'name' => 'Times New Roman',
                'lineHeight' => 1.0,
                'spaceBefore' => 0,
                'spaceAfter' => 0
            ],
            'paragraph' => [
                'indentation' => [
                    'firstLine' => 425.25
                ],
                'alignment' => Jc::BOTH,
            ],
        ],
    ],
    'tableConfig' => [
        'disciplineWidth' => 4000,
    ],
    'text' => [
        'title' => 'Результаты промежуточной аттестации',
        'teacherPrefix' => 'Преподаватель ',
        'description' => 'Результаты освоения обучающимися образовательных программ по итогам мониторингов, проводимых организацией (качество знаний с учетом статуса образовательной организации)',
        'averageScore' => 'По результатам промежуточной аттестации за межаттестационный период средний балл обучающихся составил:',
        'quality' => 'По результатам промежуточной аттестации за межаттестационный период качество знаний обучающихся составило:',
    ],
    'margins' => [
        'marginTop'    => 1134,
        'marginBottom' => 1134,
        'marginLeft'   => 1701,
        'marginRight'  => 850.5,
    ],
    'numberingStyle' => [
        'type' => 'multilevel',
        'levels' => [
            [
                'format' => 'decimal',
                'text' => '%1.',
                'left' => 360,
                'hanging' => 360
            ],
            [
                'format' => 'decimal',
                'text' => '%1.%2.',
                'left' => 480,
                'hanging' => 480
            ],
        ],
    ],
    'emptyLine' => [
        'defaultHeight' => 1.15,
        'tableSpacer' => 1.0,
    ],
];
