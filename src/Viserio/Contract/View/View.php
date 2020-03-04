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

use Viserio\Contract\Support\Renderable;

interface View extends Renderable
{
    /**
     * Get the name of the view.
     */
    public function getName(): string;

    /**
     * Get the array of view data.
     */
    public function getData(): array;

    /**
     * Get the path to the view file.
     */
    public function getPath(): string;

    /**
     * Set the path to the view.
     */
    public function setPath(string $path): self;

    /**
     * Add a piece of data to the view.
     *
     * @param array|string $key
     */
    public function with($key, $value = null): self;

    /**
     * Get the string contents of the view.
     */
    public function render(?callable $callback = null): string;

    /**
     * Add a view instance to the view data.
     *
     * @param string   $key
     * @param string[] $data
     */
    public function nest($key, string $view, array $data = []): self;

    /**
     * Get the view factory instance.
     *
     * @return \Viserio\Contract\View\Factory
     */
    public function getFactory(): Factory;

    /**
     * Get the view's rendering engine.
     *
     * @return \Viserio\Contract\View\Engine
     */
    public function getEngine(): Engine;
}
