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

namespace Viserio\Component\Filesystem\Tests\Iterator;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
abstract class AbstractBaseGlobFixtureTestCase extends TestCase
{
    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    protected $root;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();

        vfsStream::newFile('base.css')
            ->at($this->root);

        $this->root->addChild(new vfsStreamDirectory('css'));
        $cssDir = $this->root->getChild('css');

        vfsStream::newFile('reset.css')
            ->at($cssDir);
        vfsStream::newFile('style.css')
            ->at($cssDir);
        vfsStream::newFile('style.cts')
            ->at($cssDir);
        vfsStream::newFile('style.cxs')
            ->at($cssDir);

        $this->root->addChild(new vfsStreamDirectory('js'));

        vfsStream::newFile('script.js')
            ->at($this->root->getChild('js'));
    }
}
