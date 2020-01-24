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

namespace Viserio\Contract\Container\Processor;

interface ParameterProcessor
{
    /** @var string */
    public const PARAMETER_REGEX = '/\{([^\{\}|^\{|^\s]+)\}/';

    /** @var string */
    public const PROCESSOR_WITH_PLACEHOLDER_REGEX = '/(.*)\|(%s)/';

    /**
     * Option to resolve the processor only on runtime call.
     *
     * @return bool
     */
    public static function isRuntime(): bool;

    /**
     * The PHP-types managed by processor, keyed by supported prefixes.
     *
     * @return array<string, string>
     */
    public static function getProvidedTypes(): array;

    /**
     * Check if processor supports parameter.
     *
     * @param string $parameter
     *
     * @return bool
     */
    public function supports(string $parameter): bool;

    /**
     * Process parameter value through processor.
     *
     * @param string $parameter
     *
     * @throws \Viserio\Contract\Container\Exception\RuntimeException
     *
     * @return mixed
     */
    public function process(string $parameter);
}
