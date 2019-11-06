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

namespace Viserio\Contract\Parser;

interface Loader
{
    /**
     * Set directories.
     *
     * @param array $directories
     *
     * @return self
     */
    public function setDirectories(array $directories): self;

    /**
     * Get directories.
     *
     * @return array
     */
    public function getDirectories(): array;

    /**
     * Add directory.
     *
     * @param string $directory
     *
     * @return self
     */
    public function addDirectory(string $directory): self;

    /**
     * Load the given file path.
     *
     * @param string     $file
     * @param null|array $options
     *
     * @throws \Viserio\Contract\Parser\Exception\RuntimeException      if wrong options are given
     * @throws \Viserio\Contract\Parser\Exception\FileNotFoundException
     *
     * @return array
     */
    public function load(string $file, ?array $options = null): array;

    /**
     * Determine if the given file exists.
     *
     * @param string $file
     *
     * @throws \Viserio\Contract\Parser\Exception\FileNotFoundException
     *
     * @return string
     */
    public function exists(string $file): string;
}
