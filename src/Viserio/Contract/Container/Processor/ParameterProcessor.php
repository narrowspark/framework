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

namespace Viserio\Contract\Container\Processor;

interface ParameterProcessor
{
    /** @var string */
    public const PARAMETER_REGEX = '/\{([^\{\}|^\{|^\s]+)\}/';

    /** @var string */
    public const PROCESSOR_WITH_PLACEHOLDER_REGEX = '/(.*)\|(%s)/';

    /**
     * Option to resolve the processor only on runtime call.
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
     */
    public function supports(string $parameter): bool;

    /**
     * Process parameter value through processor.
     *
     * @throws \Viserio\Contract\Container\Exception\RuntimeException
     */
    public function process(string $parameter);
}
