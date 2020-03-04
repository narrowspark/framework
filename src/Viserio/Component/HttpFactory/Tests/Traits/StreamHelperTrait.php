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

namespace Viserio\Component\HttpFactory\Tests\Traits;

trait StreamHelperTrait
{
    /** @var array */
    protected static $tempFiles = [];

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        foreach (static::$tempFiles as $tempFile) {
            if (\is_file($tempFile)) {
                \unlink($tempFile);
            }
        }
    }

    /**
     * @return bool|string
     */
    protected function createTemporaryFile()
    {
        return static::$tempFiles[] = \tempnam(\sys_get_temp_dir(), 'http_factory_tests_');
    }

    /**
     * @return bool|resource
     */
    protected function createTemporaryResource($content = null)
    {
        $file = $this->createTemporaryFile();
        $resource = \fopen($file, 'r+b');

        if ($content !== null) {
            \fwrite($resource, $content);
            \rewind($resource);
        }

        return $resource;
    }
}
