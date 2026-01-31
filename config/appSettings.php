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
        'footer_text' => [
            'value' => '© 2001-2025 Minnesota Furs a 501c3 Minnesota Non-Profit • Built and maintained by local furs.',
            'type' => 'string',
            'group' => 'general',
            'label' => 'Footer Text',
            'description' => 'The text displayed in the footer of the application.',
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
    'security' => [
        'blacklist_emails' => [
            'value' => '',
            'type' => 'string',
            'group' => 'security',
            'label' => 'Blacklisted Emails',
            'description' => 'Comma-separated list of emails that cannot create accounts (e.g., spam@example.com, abuse@test.com)',
        ],
        'blacklist_names' => [
            'value' => '',
            'type' => 'string',
            'group' => 'security',
            'label' => 'Blacklisted Full Names',
            'description' => 'Comma-separated list of full names (First Last) that cannot be used (e.g., John Smith, Admin User, Test Account)',
        ],
    ],
];