<?php
return [
    'migrations_paths' => [
        'Migrations\\Core' => __DIR__ . '/core', 
    ],
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
    ],
    'all_or_nothing' => true,
    'check_database_platform' => true,
    'connection' => null,
];
