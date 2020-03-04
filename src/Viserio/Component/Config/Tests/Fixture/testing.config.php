<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

use Doctrine\DBAL\Driver\PDOMySql\Driver;

// interop config example
return [
    // vendor name
    'doctrine' => [
        // package name
        'connection' => [
            // container id
            'orm_default' => [
                // mandatory params
                'driverClass' => Driver::class,
                'params' => [
                    'host' => 'localhost',
                    'port' => '3306',
                    'user' => 'username',
                    'password' => 'password',
                    'dbname' => 'database',
                ],
            ],
        ],
        // package name
        'universal' => [
            // container id
            'orm_default' => [
                // mandatory params
                'driverClass' => Driver::class,
                'params' => [
                    'host' => 'localhost',
                    'user' => 'username',
                    'password' => 'password',
                    'dbname' => 'database',
                ],
            ],
        ],
    ],
    'one' => [
        'two' => [
            'three' => [
                'four' => [
                    'name' => 'test',
                    'class' => 'stdClass',
                ],
            ],
        ],
    ],
    // mandatory params
    'driverClass' => Driver::class,
    'params' => [
        'host' => 'localhost',
        'port' => '3306',
        'user' => 'username',
        'password' => 'password',
        'dbname' => 'database',
    ],
];
