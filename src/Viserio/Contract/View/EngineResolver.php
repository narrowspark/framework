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

namespace Viserio\Contract\View;

use Viserio\Contract\View\Engine as EngineContract;

interface EngineResolver
{
    /**
     * Set a new view engine.
     *
     * @param string                        $name
     * @param \Viserio\Contract\View\Engine $engine
     *
     * @return void
     */
    public function set(string $name, EngineContract $engine): void;

    /**
     * Loads a view engine.
     *
     * @param string $name
     *
     * @throws \Viserio\Contract\View\Exception\ViewEngineNotFoundException
     *
     * @return \Viserio\Contract\View\Engine
     */
    public function get(string $name): EngineContract;

    /**
     * Checks if a view engine exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * @return string[] All registered view engine names
     */
    public function getNames(): array;
}
