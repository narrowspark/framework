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

namespace Viserio\Component\Container\Processor;

use Viserio\Contract\Container\Exception\InvalidArgumentException;

class ConstantProcessor extends AbstractParameterProcessor
{
    /**
     * The registry of tokens.
     *
     * @var array<string, mixed>
     */
    protected $constants = [];

    /**
     * Create a new ConstantProcessor instance.
     *
     * @param bool $userOnly true to process only user-defined constants,
     *                       false to process all PHP constants; defaults to true
     */
    public function __construct(bool $userOnly = true)
    {
        if ($userOnly) {
            $constants = \get_defined_constants(true);

            $this->constants = $constants['user'] ?? [];
        } else {
            $this->constants = \get_defined_constants();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'const' => 'bool|int|float|string|array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key,, $search] = $this->getData($parameter);

        $value = null;

        if (\defined($key)) {
            $value = \constant($key);
        } elseif (\class_exists($class = \substr($key, 0, -7))) {
            $value = $class;
        } elseif (\array_key_exists($key, $this->constants)) {
            $value = $this->constants[$key];
        }

        if ($value === null) {
            throw new InvalidArgumentException(\sprintf('Constant for [%s] was not found.', $parameter));
        }

        return \str_replace($search, $value, $parameter);
    }
}
