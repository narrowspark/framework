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

namespace Viserio\Contract\Validation;

interface Validator
{
    /**
     * Add your own rule's namespace.
     */
    public function with(string $namespace): void;

    /**
     * Run the validator rules against given data.
     */
    public function validate(array $data, array $rules): self;

    /**
     * Determine if the data passes the validation rules.
     */
    public function passes(): bool;

    /**
     * Returns the data which was valid.
     */
    public function valid(): array;

    /**
     * Determine if the data fails the validation rules.
     */
    public function fails(): bool;

    /**
     * Returns the data which was invalid.
     */
    public function invalid(): array;
}
