<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Job categories
     |--------------------------------------------------------------------------
     |
     */
    'creative' => [
        'label' => 'Radošie pakalpojumi',
        'children' => [
            'creative.logo' => 'Logotipi un zīmolu dizains',
            'creative.graphic' => 'Grafiskais dizains',
            'creative.video' => 'Video un animācija',
            'creative.copy' => 'Tekstu rakstīšana un saturs',
            'creative.uiux' => 'UI / UX dizains',
        ],
    ],

    'technology' => [
        'label' => 'Tehnoloģijas',
        'children' => [
            'technology.web' => 'Web izstrāde',
            'technology.mobile' => 'Mobilās aplikācijas',
            'technology.backend' => 'Backend / API',
            'technology.devops' => 'DevOps un infrastruktūra',
            'technology.data' => 'Dati, ML un analītika',
        ],
    ],

    'education' => [
        'label' => 'Izglītība un mācība',
        'children' => [
            'education.tutoring' => 'Privātstundas',
            'education.languages' => 'Valodas',
            'education.career' => 'Karjeras konsultācijas',
            'education.design' => 'Dizaina mentorēšana',
        ],
    ],

    'professional' => [
        'label' => 'Profesionālie pakalpojumi',
        'children' => [
            'professional.accounting' => 'Grāmatvedība un uzskaites vedēšana',
            'professional.legal' => 'Juridiskie padomi',
            'professional.consulting' => 'Biznesa konsultācijas',
            'professional.hr' => 'HR un darbinieku atlase',
        ],
    ],

    'marketing' => [
        'label' => 'Mārketings un izaugsme',
        'children' => [
            'marketing.seo' => 'SEO un saturs',
            'marketing.smm' => 'Sociālie mediji',
            'marketing.ads' => 'Apmaksātā reklāma',
            'marketing.email' => 'E-pasta mārketings',
        ],
    ],

    'other' => [
        'label' => 'Citi / dažādi',
        'children' => [
            'other.general' => 'Vispārīgā palīdzība',
            'other.research' => 'Pētniecība un datu ievade',
        ],
    ],
];
