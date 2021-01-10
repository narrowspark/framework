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

namespace Viserio\Contract\View;

use Viserio\Contract\View\Engine as EngineContract;

interface EngineResolver
{
    /**
     * Set a new view engine.
     */
    public function set(string $name, EngineContract $engine): void;

    /**
     * Loads a view engine.
     *
     * @throws \Viserio\Contract\View\Exception\ViewEngineNotFoundException
     */
    public function get(string $name): EngineContract;

    /**
     * Checks if a view engine exists.
     */
    public function has(string $name): bool;

    /**
     * @return string[] All registered view engine names
     */
    public function getNames(): array;
}
