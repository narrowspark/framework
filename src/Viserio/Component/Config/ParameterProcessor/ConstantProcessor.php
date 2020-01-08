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

class ConstantProcessor extends AbstractParameterProcessor
{
    /**
     * The registry of tokens.
     *
     * @var array
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
    public static function getReferenceKeyword(): string
    {
        return 'const';
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $data)
    {
        $value = $parameterKey = self::parseParameter($data);

        if (\defined($parameterKey)) {
            $value = \constant($parameterKey);
        } elseif (\class_exists($class = \substr($parameterKey, 0, -7))) {
            $value = $class;
        } elseif (\array_key_exists($parameterKey, $this->constants)) {
            $value = $this->constants[$parameterKey];
        }

        return self::replaceData($data, $parameterKey, $value);
    }
}
