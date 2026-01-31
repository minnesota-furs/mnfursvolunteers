<?php 

return [
    'general' => [
        'application_name' => [
            'value' => 'My Application',
            'type' => 'string',
            'group' => 'general',
            'label' => 'Application Name',
            'description' => 'The name of your application displayed in the header.',
        ],
        'application_logo' => [
            'value' => 'default_logo.png',
            'type' => 'string',
            'group' => 'general',
            'label' => 'Application Logo',
            'description' => 'The logo of your application, shown in the header and emails.',
        ],
    ],
    'department_settings' => [
        'enable_department_head' => [
            'value' => true,
            'type' => 'boolean',
            'group' => 'department_settings',
            'label' => 'Enable Department Head',
            'description' => 'Turn on or off the department head feature for your organization.',
        ],
    ],
    'api_settings' => [
        'test' => [
            'value' => true,
            'type' => 'boolean',
            'group' => 'api_settings',
            'label' => 'Lorem',
            'description' => 'Turn on or off the department head feature for your organization.',
        ],
    ],
    'feature_flags' => [
        'feature_volunteer_events' => [
            'value' => true,
            'type' => 'boolean',
            'group' => 'feature_flags',
            'label' => 'Volunteer Events',
            'description' => 'Enable or disable the volunteer events system.',
            'beta' => false,
        ],
        'feature_elections' => [
            'value' => true,
            'type' => 'boolean',
            'group' => 'feature_flags',
            'label' => 'Elections',
            'description' => 'Enable or disable the elections module.',
            'beta' => false,
        ],
        'feature_job_listings' => [
            'value' => true,
            'type' => 'boolean',
            'group' => 'feature_flags',
            'label' => 'Job Listings',
            'description' => 'Enable or disable the job listings feature.',
            'beta' => false,
        ],
        'feature_one_off_events' => [
            'value' => true,
            'type' => 'boolean',
            'group' => 'feature_flags',
            'label' => 'One-Off Events',
            'description' => 'Enable or disable one-off event check-ins. Great for meetings or special events.',
            'beta' => true,
        ],
        'feature_wordpress_integration' => [
            'value' => true,
            'type' => 'boolean',
            'group' => 'feature_flags',
            'label' => 'WordPress Integration',
            'description' => 'Enable or disable WordPress user authentication and linking.',
            'beta' => false,
        ],
    ],
];