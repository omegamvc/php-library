<?php

use System\Integrate\Bootstrap\TestVendorServiceProvider;

return [
    'omega/firstpackage' => [
        'providers' => [
            TestVendorServiceProvider::class,
        ],
    ],
];
