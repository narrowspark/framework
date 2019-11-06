<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Cake\Chronos\Chronos;
use Cake\Chronos\Date;
use Cake\Chronos\MutableDate;
use Cake\Chronos\MutableDateTime;
use const DIRECTORY_SEPARATOR;
use function date_default_timezone_set;
use function dirname;

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
date_default_timezone_set('UTC');

Chronos::setTestNow(Chronos::now());
MutableDateTime::setTestNow(MutableDateTime::now());
Date::setTestNow(Date::now());
MutableDate::setTestNow(MutableDate::now());
