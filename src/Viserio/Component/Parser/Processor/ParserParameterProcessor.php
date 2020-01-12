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

namespace Viserio\Component\Parser\Processor;

use Viserio\Component\Config\Processor\AbstractParameterProcessor;

class ParserParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'parse' => 'array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter): void
    {
        [$key,, $search] = $this->getData($parameter);
    }
}
