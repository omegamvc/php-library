<?php

$array = [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'credentials' => [
            'username' => 'root',
            'password' => 'password123',
        ],
        'options' => [
            'charset' => 'utf8mb4',
            'timezone' => 'UTC',
        ],
    ],
    'cache' => [
        'enabled' => true,
        'driver' => 'redis',
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 5,
        ],
    ],
    'app' => [
        'name' => 'MyApp',
        'env' => 'production',
        'debug' => false,
        'timezone' => 'Europe/Rome',
        'key' => 'base64:ABCD1234==',
    ],
    'users' => [
        [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'roles' => ['admin', 'user'],
            'active' => true,
        ],
        [
            'id' => 2,
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'roles' => ['user'],
            'active' => false,
        ],
    ],
    'settings' => [
        'theme' => 'dark',
        'language' => 'en',
        'notifications' => [
            'email' => true,
            'sms' => false,
            'push' => true,
        ],
        'privacy' => [
            'tracking' => false,
            'ads' => true,
        ],
    ],
    'logs' => [
        'level' => 'info',
        'path' => '/var/logs/app.log',
        'rotation' => [
            'enabled' => true,
            'max_size' => '100MB',
            'retain' => 10,
        ],
    ],
    'features' => [
        'feature1' => true,
        'feature2' => false,
        'feature3' => [
            'subfeature1' => true,
            'subfeature2' => false,
        ],
    ],
    'pricing' => [
        'currency' => 'USD',
        'tax_rate' => 0.20,
        'items' => [
            'basic' => 19.99,
            'premium' => 49.99,
            'enterprise' => 99.99,
        ],
    ],
    'meta' => [
        'version' => '1.0.0',
        'release_date' => '2025-01-01',
        'authors' => ['Alice', 'Bob'],
    ],
    'nested' => [
        'level1' => [
            'level2' => [
                'level3' => [
                    'key' => 'value',
                ],
            ],
        ],
    ],
    'null_value' => null,
    'boolean_value' => true,
    'float_value' => 3.14159,
    'integer_value' => 42,
    'string_value' => 'Hello, world!',
];

require 'ConfigRepositoryInterface.php';
require 'ConfigRepository.php';

$repo = new ConfigRepository($array);

print_r($repo);
