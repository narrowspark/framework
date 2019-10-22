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

namespace Viserio\Contract\Validation;

interface Validator
{
    /**
     * Add your own rule's namespace.
     *
     * @param string $namespace
     *
     * @return void
     */
    public function with(string $namespace): void;

    /**
     * Run the validator rules against given data.
     *
     * @param array $data
     * @param array $rules
     *
     * @return self
     */
    public function validate(array $data, array $rules): self;

    /**
     * Determine if the data passes the validation rules.
     *
     * @return bool
     */
    public function passes(): bool;

    /**
     * Returns the data which was valid.
     *
     * @return array
     */
    public function valid(): array;

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails(): bool;

    /**
     * Returns the data which was invalid.
     *
     * @return array
     */
    public function invalid(): array;
}
