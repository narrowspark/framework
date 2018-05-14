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

use Viserio\Component\Container\Tests\Fixture\Alias\AliasEnvFixture;

class Alias_EnvFixture
{
}

\class_alias('Alias_EnvFixture', AliasEnvFixture::class, false);
