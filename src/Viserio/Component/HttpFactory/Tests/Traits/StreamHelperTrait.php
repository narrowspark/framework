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
     * @param mixed $content
     *
     * @return bool|resource
     */
    protected function createTemporaryResource($content = null)
    {
        $file = $this->createTemporaryFile();
        $resource = \fopen($file, 'r+');

        if ($content !== null) {
            \fwrite($resource, $content);
            \rewind($resource);
        }

        return $resource;
    }
}
