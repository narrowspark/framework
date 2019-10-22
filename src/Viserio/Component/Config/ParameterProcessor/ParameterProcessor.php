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

namespace Viserio\Component\Config\ParameterProcessor;

use Narrowspark\Arr\Arr;

class ParameterProcessor extends AbstractParameterProcessor
{
    /**
     * A array of parameters.
     *
     * @var array
     */
    private $parameters;

    /**
     * Create a new ParameterProcessor instance.
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
    public static function getReferenceKeyword(): string
    {
        return 'parameter';
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $data)
    {
        $parameterKey = $this->parseParameter($data);

        return $this->replaceData($data, $parameterKey, Arr::get($this->parameters, $parameterKey));
    }
}
