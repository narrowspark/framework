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

use Viserio\Component\Container\Tests\Fixture\Alias\AliasEnvFixture;

class Alias_EnvFixture
{
}

\class_alias('Alias_EnvFixture', AliasEnvFixture::class, false);
