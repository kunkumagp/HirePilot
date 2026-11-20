<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Job category taxonomy
|--------------------------------------------------------------------------
|
| Two-level taxonomy used by the Settings UI. Each sub-category carries the
| keywords that the scoring service looks for in job titles and descriptions.
|
*/

return [
    'categories' => [
        'engineering' => [
            'label' => 'Engineering & Software',
            'keywords' => ['engineer', 'developer', 'software', 'programmer'],
            'subcategories' => [
                'frontend' => ['label' => 'Frontend Development', 'keywords' => ['frontend', 'front-end', 'react', 'vue', 'angular', 'javascript', 'typescript']],
                'backend' => ['label' => 'Backend Development', 'keywords' => ['backend', 'back-end', 'node', 'laravel', 'django', 'spring', 'rails', 'python']],
                'fullstack' => ['label' => 'Full Stack Development', 'keywords' => ['full stack', 'fullstack', 'full-stack']],
                'mobile' => ['label' => 'Mobile Development', 'keywords' => ['mobile', 'ios', 'android', 'react native', 'flutter', 'swift', 'kotlin']],
                'devops' => ['label' => 'DevOps & SRE', 'keywords' => ['devops', 'sre', 'kubernetes', 'docker', 'terraform', 'ci/cd', 'aws', 'azure']],
                'data_engineering' => ['label' => 'Data Engineering', 'keywords' => ['data engineer', 'etl', 'spark', 'airflow', 'warehouse', 'pipeline']],
                'qa_testing' => ['label' => 'QA & Testing', 'keywords' => ['qa', 'quality assurance', 'automation test', 'selenium', 'playwright', 'manual test']],
                'security' => ['label' => 'Cybersecurity', 'keywords' => ['security', 'cyber', 'penetration', 'soc', 'iam', 'vulnerability']],
                'ml_ai' => ['label' => 'AI & Machine Learning', 'keywords' => ['machine learning', 'ml engineer', 'ai engineer', 'llm', 'nlp', 'model training']],
                'blockchain' => ['label' => 'Blockchain & Web3', 'keywords' => ['blockchain', 'web3', 'solidity', 'smart contract', 'crypto']],
                'game_dev' => ['label' => 'Game Development', 'keywords' => ['game developer', 'unity', 'unreal', 'gameplay', 'gamedev']],
                'embedded' => ['label' => 'Embedded Systems', 'keywords' => ['embedded', 'firmware', 'c++', 'rtos', 'microcontroller']],
            ],
        ],

        'design' => [
            'label' => 'Design & Creative',
            'keywords' => ['designer', 'design', 'creative'],
            'subcategories' => [
                'ui_ux' => ['label' => 'UI / UX Design', 'keywords' => ['ui', 'ux', 'user experience', 'figma', 'wireframe', 'interaction design']],
                'product_design' => ['label' => 'Product Design', 'keywords' => ['product designer', 'product design', 'design system']],
                'graphic' => ['label' => 'Graphic Design', 'keywords' => ['graphic designer', 'illustrator', 'photoshop', 'branding']],
                'web_design' => ['label' => 'Web Design', 'keywords' => ['web designer', 'web design', 'landing page']],
                'motion' => ['label' => 'Motion & Animation', 'keywords' => ['motion', 'animation', 'after effects', '2d', '3d animation']],
                'video' => ['label' => 'Video & Editing', 'keywords' => ['video editor', 'video editing', 'premiere', 'filmmaker']],
                '3d' => ['label' => '3D & Modeling', 'keywords' => ['3d', 'blender', 'maya', '3d modeling', 'rendering']],
            ],
        ],

        'product' => [
            'label' => 'Product Management',
            'keywords' => ['product manager', 'product owner', 'product management'],
            'subcategories' => [
                'product_manager' => ['label' => 'Product Manager', 'keywords' => ['product manager', 'pm', 'product roadmap']],
                'product_owner' => ['label' => 'Product Owner', 'keywords' => ['product owner', 'scrum', 'backlog', 'agile']],
                'product_analyst' => ['label' => 'Product Analyst', 'keywords' => ['product analyst', 'product analytics', 'a/b test']],
                'product_ops' => ['label' => 'Product Operations', 'keywords' => ['product operations', 'product ops', 'product program']],
            ],
        ],

        'data_analytics' => [
            'label' => 'Data & Analytics',
            'keywords' => ['data', 'analytics', 'analyst', 'intelligence'],
            'subcategories' => [
                'data_analyst' => ['label' => 'Data Analyst', 'keywords' => ['data analyst', 'sql', 'tableau', 'power bi', 'looker']],
                'business_intelligence' => ['label' => 'Business Intelligence', 'keywords' => ['business intelligence', 'bi', 'dashboard', 'reporting']],
                'data_scientist' => ['label' => 'Data Scientist', 'keywords' => ['data scientist', 'statistics', 'python', 'r', 'predictive']],
                'analytics_engineer' => ['label' => 'Analytics Engineer', 'keywords' => ['analytics engineer', 'dbt', 'data model', 'semantic layer']],
            ],
        ],

        'marketing' => [
            'label' => 'Marketing & Growth',
            'keywords' => ['marketing', 'growth', 'seo', 'campaign'],
            'subcategories' => [
                'digital_marketing' => ['label' => 'Digital Marketing', 'keywords' => ['digital marketing', 'online marketing', 'marketing manager']],
                'seo' => ['label' => 'SEO & Content Marketing', 'keywords' => ['seo', 'search engine', 'content marketing', 'keyword']],
                'growth' => ['label' => 'Growth Marketing', 'keywords' => ['growth marketing', 'growth hacker', 'acquisition', 'retention']],
                'email_marketing' => ['label' => 'Email Marketing', 'keywords' => ['email marketing', 'mailchimp', 'klaviyo', 'email campaign']],
                'social_media' => ['label' => 'Social Media', 'keywords' => ['social media', 'facebook ads', 'instagram', 'linkedin marketing', 'tiktok']],
                'paid_ads' => ['label' => 'Paid Advertising', 'keywords' => ['paid ads', 'ppc', 'google ads', 'facebook ads', 'ad spend']],
                'product_marketing' => ['label' => 'Product Marketing', 'keywords' => ['product marketing', 'positioning', 'launch', 'gtm']],
                'marketing_analytics' => ['label' => 'Marketing Analytics', 'keywords' => ['marketing analytics', 'attribution', 'campaign reporting', 'hubspot']],
            ],
        ],

        'sales' => [
            'label' => 'Sales & Business Development',
            'keywords' => ['sales', 'business development', 'account executive'],
            'subcategories' => [
                'account_executive' => ['label' => 'Account Executive', 'keywords' => ['account executive', 'ae', 'enterprise sales', 'quota']],
                'business_development' => ['label' => 'Business Development', 'keywords' => ['business development', 'bdr', 'partnerships', 'new business']],
                'sdr' => ['label' => 'SDR / BDR', 'keywords' => ['sdr', 'bdr', 'sales development', 'prospecting', 'cold outreach']],
                'account_manager' => ['label' => 'Account Manager', 'keywords' => ['account manager', 'customer account', 'renewals', 'upsell']],
                'sales_manager' => ['label' => 'Sales Manager', 'keywords' => ['sales manager', 'sales lead', 'team lead', 'sales director']],
                'inside_sales' => ['label' => 'Inside Sales', 'keywords' => ['inside sales', 'remote sales', 'telescope', 'phone sales']],
                'solutions_consultant' => ['label' => 'Solutions Consultant', 'keywords' => ['solutions consultant', 'sales engineer', 'pre-sales', 'demo']],
            ],
        ],

        'customer' => [
            'label' => 'Customer Support & Success',
            'keywords' => ['customer', 'support', 'success', 'service'],
            'subcategories' => [
                'customer_support' => ['label' => 'Customer Support', 'keywords' => ['customer support', 'customer service', 'help desk', 'ticket']],
                'customer_success' => ['label' => 'Customer Success', 'keywords' => ['customer success', 'csm', 'onboarding', 'retention']],
                'technical_support' => ['label' => 'Technical Support', 'keywords' => ['technical support', 'tech support', 'troubleshooting', 'desk']],
                'call_center' => ['label' => 'Call Center', 'keywords' => ['call center', 'bpo', 'inbound', 'outbound']],
                'cx_manager' => ['label' => 'Customer Experience Manager', 'keywords' => ['customer experience', 'cx manager', 'voice of customer']],
            ],
        ],

        'operations' => [
            'label' => 'Operations & Project Management',
            'keywords' => ['operations', 'project manager', 'program manager', 'coordinator'],
            'subcategories' => [
                'project_manager' => ['label' => 'Project Manager', 'keywords' => ['project manager', 'pmp', 'scrum master', 'delivery manager']],
                'program_manager' => ['label' => 'Program Manager', 'keywords' => ['program manager', 'portfolio', 'multi-project']],
                'business_operations' => ['label' => 'Business Operations', 'keywords' => ['business operations', 'bizops', 'process improvement']],
                'supply_chain' => ['label' => 'Supply Chain', 'keywords' => ['supply chain', 'procurement', 'vendor management', 'inventory']],
                'virtual_assistant' => ['label' => 'Virtual Assistant', 'keywords' => ['virtual assistant', 'executive assistant', 'administrative assistant']],
            ],
        ],

        'finance' => [
            'label' => 'Finance & Accounting',
            'keywords' => ['finance', 'accounting', 'accountant', 'financial'],
            'subcategories' => [
                'accountant' => ['label' => 'Accountant', 'keywords' => ['accountant', 'bookkeeper', 'general ledger', 'reconciliation']],
                'financial_analyst' => ['label' => 'Financial Analyst', 'keywords' => ['financial analyst', 'fp&a', 'forecasting', 'valuation']],
                'payroll' => ['label' => 'Payroll', 'keywords' => ['payroll', 'salary processing', 'payslip']],
                'tax' => ['label' => 'Tax & Audit', 'keywords' => ['tax', 'audit', 'compliance', 'taxation']],
            ],
        ],

        'hr' => [
            'label' => 'Human Resources',
            'keywords' => ['human resources', 'hr', 'recruiter', 'talent'],
            'subcategories' => [
                'recruiter' => ['label' => 'Recruiter', 'keywords' => ['recruiter', 'talent acquisition', 'sourcing', 'hiring']],
                'hr_generalist' => ['label' => 'HR Generalist', 'keywords' => ['hr generalist', 'human resources', 'employee relations']],
                'people_ops' => ['label' => 'People Operations', 'keywords' => ['people operations', 'people ops', 'hr operations']],
                'hr_manager' => ['label' => 'HR Manager', 'keywords' => ['hr manager', 'head of people', 'hr business partner']],
                'training' => ['label' => 'Learning & Development', 'keywords' => ['learning and development', 'training', 'l&d', 'onboarding']],
            ],
        ],

        'writing' => [
            'label' => 'Writing & Content',
            'keywords' => ['writer', 'writing', 'content', 'editor'],
            'subcategories' => [
                'content_writer' => ['label' => 'Content Writer', 'keywords' => ['content writer', 'blog writer', 'article writer', 'copy']],
                'copywriter' => ['label' => 'Copywriter', 'keywords' => ['copywriter', 'ad copy', 'marketing copy', 'tagline']],
                'technical_writer' => ['label' => 'Technical Writer', 'keywords' => ['technical writer', 'documentation', 'api docs', 'manual']],
                'editor' => ['label' => 'Editor', 'keywords' => ['editor', 'proofreader', 'content editor']],
                'translator' => ['label' => 'Translator', 'keywords' => ['translator', 'localization', 'translation', 'bilingual']],
            ],
        ],

        'education' => [
            'label' => 'Education & Training',
            'keywords' => ['teacher', 'tutor', 'education', 'instructor'],
            'subcategories' => [
                'teacher' => ['label' => 'Teacher', 'keywords' => ['teacher', 'classroom', 'curriculum', 'students']],
                'tutor' => ['label' => 'Online Tutor', 'keywords' => ['tutor', 'online tutor', 'homework help']],
                'instructor' => ['label' => 'Training Instructor', 'keywords' => ['instructor', 'trainer', 'workshop', 'bootcamp']],
                'curriculum' => ['label' => 'Curriculum Developer', 'keywords' => ['curriculum developer', 'course design', 'lesson plan']],
            ],
        ],

        'healthcare' => [
            'label' => 'Healthcare & Medical',
            'keywords' => ['healthcare', 'medical', 'nurse', 'doctor', 'clinical'],
            'subcategories' => [
                'nurse' => ['label' => 'Nursing', 'keywords' => ['nurse', 'rn', 'lpn', 'patient care']],
                'doctor' => ['label' => 'Physician', 'keywords' => ['doctor', 'physician', 'md', 'specialist']],
                'therapy' => ['label' => 'Therapy & Allied Health', 'keywords' => ['therapist', 'physiotherapist', 'psychologist', 'occupational therapist']],
                'medical_admin' => ['label' => 'Medical Administration', 'keywords' => ['medical admin', 'healthcare administrator', 'clinic manager']],
                'telehealth' => ['label' => 'Telehealth', 'keywords' => ['telehealth', 'virtual care', 'remote consultation']],
            ],
        ],

        'legal' => [
            'label' => 'Legal & Compliance',
            'keywords' => ['legal', 'lawyer', 'attorney', 'compliance'],
            'subcategories' => [
                'lawyer' => ['label' => 'Lawyer / Attorney', 'keywords' => ['lawyer', 'attorney', 'counsel', 'litigation']],
                'paralegal' => ['label' => 'Paralegal', 'keywords' => ['paralegal', 'legal assistant', 'legal support']],
                'compliance' => ['label' => 'Compliance Officer', 'keywords' => ['compliance officer', 'regulatory', 'kyc', 'aml']],
                'contracts' => ['label' => 'Contract Management', 'keywords' => ['contract manager', 'contracts', 'legal operations']],
            ],
        ],

        'real_estate' => [
            'label' => 'Real Estate & Construction',
            'keywords' => ['real estate', 'property', 'construction', 'architect'],
            'subcategories' => [
                'agent' => ['label' => 'Real Estate Agent', 'keywords' => ['real estate agent', 'broker', 'property sales']],
                'property_manager' => ['label' => 'Property Manager', 'keywords' => ['property manager', 'facilities', 'lease']],
                'architect' => ['label' => 'Architect', 'keywords' => ['architect', 'architecture', 'autocad', 'revit']],
                'civil_engineer' => ['label' => 'Civil Engineer', 'keywords' => ['civil engineer', 'structural', 'site engineer']],
                'construction_manager' => ['label' => 'Construction Manager', 'keywords' => ['construction manager', 'site manager', 'foreman']],
            ],
        ],

        'it_support' => [
            'label' => 'IT & Infrastructure Support',
            'keywords' => ['it support', 'system administrator', 'network', 'helpdesk'],
            'subcategories' => [
                'it_support' => ['label' => 'IT Support', 'keywords' => ['it support', 'helpdesk', 'desktop support', 'service desk']],
                'sysadmin' => ['label' => 'System Administrator', 'keywords' => ['system administrator', 'sysadmin', 'linux administration', 'windows server']],
                'network' => ['label' => 'Network Engineer', 'keywords' => ['network engineer', 'networking', 'cisco', 'routing', 'switching']],
                'cloud' => ['label' => 'Cloud Engineer', 'keywords' => ['cloud engineer', 'aws', 'azure', 'gcp', 'cloud infrastructure']],
            ],
        ],

        'manufacturing' => [
            'label' => 'Manufacturing & Industrial',
            'keywords' => ['manufacturing', 'industrial', 'production', 'quality engineer'],
            'subcategories' => [
                'mechanical' => ['label' => 'Mechanical Engineer', 'keywords' => ['mechanical engineer', 'cad', 'solidworks', 'manufacturing']],
                'electrical' => ['label' => 'Electrical Engineer', 'keywords' => ['electrical engineer', 'electronics', 'plc', 'automation']],
                'quality' => ['label' => 'Quality Engineer', 'keywords' => ['quality engineer', 'six sigma', 'iso 9001', 'inspection']],
                'production' => ['label' => 'Production Supervisor', 'keywords' => ['production supervisor', 'factory', 'line manager', 'lean']],
            ],
        ],

        'logistics' => [
            'label' => 'Transport & Logistics',
            'keywords' => ['logistics', 'transport', 'warehouse', 'fleet'],
            'subcategories' => [
                'dispatcher' => ['label' => 'Dispatcher', 'keywords' => ['dispatcher', 'route planning', 'fleet coordination']],
                'warehouse' => ['label' => 'Warehouse Operations', 'keywords' => ['warehouse', 'fulfillment', 'picking', 'inventory']],
                'fleet_manager' => ['label' => 'Fleet Manager', 'keywords' => ['fleet manager', 'vehicle maintenance', 'driver management']],
                'driver' => ['label' => 'Driver', 'keywords' => ['driver', 'delivery driver', 'truck driver', 'cdl']],
            ],
        ],
    ],
];
