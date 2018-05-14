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

use Viserio\Contract\Support\Renderable;

interface View extends Renderable
{
    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the array of view data.
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Get the path to the view file.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Set the path to the view.
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath(string $path): self;

    /**
     * Add a piece of data to the view.
     *
     * @param array|string $key
     * @param mixed        $value
     *
     * @return self
     */
    public function with($key, $value = null): self;

    /**
     * Get the string contents of the view.
     *
     * @param null|callable $callback
     *
     * @return string
     */
    public function render(callable $callback = null): string;

    /**
     * Add a view instance to the view data.
     *
     * @param string   $key
     * @param string   $view
     * @param string[] $data
     *
     * @return self
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
