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

namespace Viserio\Component\Config\Processor;

use Narrowspark\Arr\Arr;

class ResolveParameterProcessor extends AbstractParameterProcessor
{
    /**
     * A array of parameters.
     *
     * @var array
     */
    private $parameters;

    /**
     * Create a new ResolveParameterProcessor instance.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'resolve' => 'string',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key,, $search] = $this->getData($parameter);

        return \str_replace($search, Arr::get($this->parameters, $key), $parameter);
    }
}
