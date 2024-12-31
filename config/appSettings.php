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
];