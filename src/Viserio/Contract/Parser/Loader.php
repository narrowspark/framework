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

namespace Viserio\Contract\Parser;

interface Loader
{
    /**
     * Set directories.
     *
     * @param array<int|string, string> $directories
     */
    public function setDirectories(array $directories): self;

    /**
     * Get directories.
     *
     * @return array<int|string, string>
     */
    public function getDirectories(): array;

    /**
     * Add directory.
     */
    public function addDirectory(string $directory): self;

    /**
     * Load the given file path.
     *
     * @param null|array<string, string> $options
     *
     * @throws \Viserio\Contract\Parser\Exception\RuntimeException      if wrong options are given
     * @throws \Viserio\Contract\Parser\Exception\FileNotFoundException
     * @throws \Viserio\Contract\Parser\Exception\NotSupportedException if a option is not supported
     *
     * @return array<int|string, mixed>
     */
    public function load(string $file, ?array $options = null): array;

    /**
     * Determine if the given file exists.
     *
     * @throws \Viserio\Contract\Parser\Exception\FileNotFoundException
     */
    public function exists(string $file): string;
}
