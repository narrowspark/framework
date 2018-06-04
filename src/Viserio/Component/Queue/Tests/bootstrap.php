<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Cake\Chronos\Chronos;

/*
|--------------------------------------------------------------------------
| Set The Default Timezone
|--------------------------------------------------------------------------
|
| Here we will set the default timezone for PHP. PHP is notoriously mean
| if the timezone is not explicitly set. This will be used by each of
| the PHP date and date-time functions throughout the application.
|
 */
\date_default_timezone_set('UTC');

Chronos::setTestNow(Chronos::now());
