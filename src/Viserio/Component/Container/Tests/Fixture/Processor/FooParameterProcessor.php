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
