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

namespace Viserio\Contract\Config;

interface RequiresValidatedConfig extends RequiresConfig
{
    public const VALIDATE_RESOURCE = 'resource';
    public const VALIDATE_CALLABLE = 'callable';
    public const VALIDATE_INT = 'int';
    public const VALIDATE_BOOL = 'bool';
    public const VALIDATE_FLOAT = 'float';
    public const VALIDATE_STRING = 'string';
    public const VALIDATE_OBJECT = 'object';
    public const VALIDATE_ARRAY = 'array';
    public const VALIDATE_NULL = 'null';

    /**
     * Returns a list of callable validators
     * which key should be available in getMandatoryOptions or getDefaultOptions.
     *
     * - the key is the option name
     * - the value is a callable or a array with predefined validators.
     *
     * Callable have the following signature:
     *        function($optionValue) for custom validators,
     *        function($optionValue, $optionKey) for custom validators
     *
     * Array have the following signature:
     *        ['string'],
     *        ['string', 'null'], for more then one validator check;
     *        [RequiresValidatedConfig::VALIDATE_STRING]
     *
     * @return array
     */
    public static function getConfigValidators(): iterable;
}
