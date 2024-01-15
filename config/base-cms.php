<?php

return [
    'welcome_page' => env('WELCOME_PAGE', ''),
    'default_front' => env('DEFAULT_FRONT', ''),
    'default_prefix' => env('DEFAULT_PREFIX', 'mysql-'),
    'recover' => env('DEFAULT_RECUPERAR', 'RECUPERAR'),

    'authenticated' => [
        'api.login.logout',
        'api.user-settings.store',
        'api.x-files.destroy',
        'api.visor-log-errors.store',
    ],
];
