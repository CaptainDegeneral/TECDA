<?php

use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

return [
    'layout' => [
        'minChartWidthColumns' => 10,
        'minChartEndRow' => 25,
        'chartStartRow' => 2,
        'chartWidthPadding' => 5,
        'chartColumnOffset' => 2,
    ],
    'chartTypes' => [
        'quality' => [
            'sheetTitle' => 'QualityResults',
            'chartTitle' => 'Уровень качества знаний',
            'dataRange' => '$B$2:$B$',
            'categoryRange' => '$A$2:$A$',
        ],
        'averageScore' => [
            'sheetTitle' => 'AverageScoreResults',
            'chartTitle' => 'Средний балл',
            'dataRange' => '$C$2:$C$',
            'categoryRange' => '$A$2:$A$',
        ],
    ],
    'periodsChartTypes' => [
        'quality' => [
            'sheetTitle' => 'QualityPerPeriods',
            'chartTitle' => 'Уровень качества знаний по годам',
            'isPercentage' => true,
            'tableTitle' => 'Уровень качества знаний',
            'tableKey' => 'qualityTable',
        ],
        'averageScore' => [
            'sheetTitle' => 'AverageScorePerPeriods',
            'chartTitle' => 'Средний балл по годам',
            'isPercentage' => false,
            'tableTitle' => 'Средний балл',
            'tableKey' => 'averageScoreTable',
        ],
    ],
    'overallTablesHeaders' => [
        'discipline' => 'Дисциплина',
        'quality' => 'Уровень качества знаний',
        'averageScore' => 'Средний балл',
    ],
    'styles' => [
        'header' => [
            'font' => [
                'bold' => true,
                'size' => 11
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ],
    ],
    'chartSettings' => [
        'barChartName' => 'Chart',
        'clusteredChartNamePrefix' => 'chart_',
        'xAxis' => [
            'textRotation' => -45,
        ],
        'yAxis' => [
            'gridLineColor' => 'd9d9d9',
        ],
        'layout' => [
            'showValues' => true,
        ],
        'legend' => [
            'position' => Legend::POSITION_BOTTOM,
            'showBorder' => false,
        ],
        'dataStartRow' => 3,
    ],
    'dataProcessing' => [
        'qualityDivider' => 100,
        'disciplineColumnTitle' => 'Дисциплина',
    ],
];
