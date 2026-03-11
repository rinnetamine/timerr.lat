<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Job categories
     |--------------------------------------------------------------------------
     |
     */
    'creative' => [
        'label' => 'Creative Services',
        'children' => [
            'creative.logo' => 'Logo & Branding',
            'creative.graphic' => 'Graphic Design',
            'creative.video' => 'Video & Animation',
            'creative.copy' => 'Copywriting & Content',
            'creative.uiux' => 'UI / UX Design',
        ],
    ],

    'technology' => [
        'label' => 'Technology',
        'children' => [
            'technology.web' => 'Web Development',
            'technology.mobile' => 'Mobile Apps',
            'technology.backend' => 'Backend / APIs',
            'technology.devops' => 'DevOps & Infrastructure',
            'technology.data' => 'Data, ML & Analytics',
        ],
    ],

    'education' => [
        'label' => 'Education & Coaching',
        'children' => [
            'education.tutoring' => 'Tutoring',
            'education.languages' => 'Languages',
            'education.career' => 'Career Coaching',
            'education.design' => 'Design Mentoring',
        ],
    ],

    'professional' => [
        'label' => 'Professional Services',
        'children' => [
            'professional.accounting' => 'Accounting & Bookkeeping',
            'professional.legal' => 'Legal Advice',
            'professional.consulting' => 'Business Consulting',
            'professional.hr' => 'HR & Recruiting',
        ],
    ],

    'marketing' => [
        'label' => 'Marketing & Growth',
        'children' => [
            'marketing.seo' => 'SEO & Content',
            'marketing.smm' => 'Social Media',
            'marketing.ads' => 'Paid Advertising',
            'marketing.email' => 'Email Marketing',
        ],
    ],

    'other' => [
        'label' => 'Other / Misc',
        'children' => [
            'other.general' => 'General Help',
            'other.research' => 'Research & Data Entry',
        ],
    ],
];
