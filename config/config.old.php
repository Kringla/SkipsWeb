<?php
// config/config.php
return [
    'db' => [
        'host'    => 'localhost',
        'name'    => 'SkipDB',
        'user'    => 'dbuser',
        'pass'    => 'dbpass',
        'charset' => 'utf8mb4',
    ],
    'auth' => [
        'roles' => ['USR', 'EDIT', 'ADMIN'],
    ],
];