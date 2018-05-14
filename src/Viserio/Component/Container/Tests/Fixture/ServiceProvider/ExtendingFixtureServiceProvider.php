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

namespace Viserio\Component\Container\Tests\Fixture\ServiceProvider;

use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;

class ExtendingFixtureServiceProvider implements ExtendServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            'previous' => static function (DefinitionContract $definition): void {
                $definition->setValue($definition->getValue() . $definition->getValue());
            },
        ];
    }
}
