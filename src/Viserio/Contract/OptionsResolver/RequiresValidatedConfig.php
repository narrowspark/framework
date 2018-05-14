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

namespace Viserio\Contract\OptionsResolver;

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
    public static function getOptionValidators(): array;
}
