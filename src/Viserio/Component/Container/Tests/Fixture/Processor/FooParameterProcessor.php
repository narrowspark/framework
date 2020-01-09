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

namespace Viserio\Component\Container\Tests\Fixture\Processor;

use Viserio\Component\Container\Processor\AbstractParameterProcessor;

class FooParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return ['foo' => 'string'];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $data)
    {
        return 'foo';
    }
}
