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

namespace Viserio\Component\Filesystem\Tests;

use Narrowspark\TestingHelper\Traits\AssertArrayTrait;
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Throwable;

/**
 * @internal
 *
 * @small
 */
final class FilesystemVfsStreamTest extends AbstractFilesystemTestCase
{
    use AssertArrayTrait;

    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    private $root;

    /**
     * {@inheritdoc}
     */
    protected $skippedTests = [
        'testCopyDirectoryMovesEntireDirectory' => 'realpath dont support stream wrappers',
        'testSetGroupSymlink' => 'symlink dont support stream wrappers',
        'testSetGroupLink' => 'symlink dont support stream wrappers',
        'testSymlink' => 'symlink dont support stream wrappers',
        'testSymlinkIsOverwrittenIfPointsToDifferentTarget' => 'symlink dont support stream wrappers',
        'testSymlinkIsNotOverwrittenIfAlreadyCreated' => 'symlink dont support stream wrappers',
        'testSymlinkCreatesTargetDirectoryIfItDoesNotExist' => 'symlink dont support stream wrappers',
        'testLink' => 'symlink dont support stream wrappers',
        'testLinkIsOverwrittenIfPointsToDifferentTarget' => 'symlink dont support stream wrappers',
        'testLinkIsNotOverwrittenIfAlreadyCreated' => 'symlink dont support stream wrappers',
        'testReadRelativeLink' => 'symlink dont support stream wrappers',
        'testReadAbsoluteLink' => 'symlink dont support stream wrappers',
        'testReadBrokenLink' => 'symlink dont support stream wrappers',
        'testReadLinkCanonicalizePath' => 'symlink dont support stream wrappers',
        'testMirrorCopiesLinks' => 'symlink dont support stream wrappers',
        'testMirrorCopiesLinkedDirectoryContents' => 'symlink dont support stream wrappers',
        'testMirrorCopiesRelativeLinkedContents' => 'chdir dont support stream wrappers',
        'testMirrorContentsWithSameNameAsSourceOrTargetWithoutDeleteOption' => 'chdir dont support stream wrappers',
        'testMirrorContentsWithSameNameAsSourceOrTargetWithDeleteOption' => 'chdir dont support stream wrappers',
        'testMirrorAvoidCopyingTargetDirectoryIfInSourceDirectory' => 'symlink dont support stream wrappers',
        'testSetGroup' => 'chgrp dont support stream wrappers', // @todo check this'
        'testCopyDoesNotOverrideExistingFileByDefault' => 'setting modification time is not working',
        'testHasThrowException' => '',
        'testSetOwner' => 'chown dont support stream wrappers',
    ];

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->umask = \umask(0);

        $this->filesystem = $this->getFilesystem();

        $this->root = vfsStream::setup();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        foreach ($this->longPathNamesWindows as $path) {
            \exec('DEL ' . $path);
        }

        $this->longPathNamesWindows = [];

        \umask($this->umask);

        \clearstatcache(false, $this->root->url());

        try {
            $this->filesystem->delete($this->root->url());
            $this->filesystem->deleteDirectory($this->root->url());
        } catch (Throwable $exception) {
            // @ignoreException
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createFile(
        string $name,
        ?string $content = null,
        $at = null,
        ?int $chmod = null,
        ?int $chgrp = null,
        ?int $time = null
    ): string {
        $file = vfsStream::newFile($name);

        if ($content !== null) {
            $file->setContent($content);
        }

        if ($at !== null) {
            $file->at($this->getDir($at));
        } else {
            $file->at($this->root);
        }

        if ($chmod !== null) {
            $file->chmod($chmod);
        }

        if ($chgrp !== null) {
            $file->chgrp($chgrp);
        }

        if ($time !== null) {
            $file->lastModified($time);
        }

        return $file->url();
    }

    /**
     * {@inheritdoc}
     */
    protected function createDir(string $name, ?string $childOf = null, ?int $chmod = null): string
    {
        $dir = $this->root;

        if ($name === 'root') {
            if ($chmod !== null) {
                $dir->chmod($chmod);
            }

            return $dir->url();
        }

        if ($childOf !== null) {
            $dir = $this->getDir($childOf);
        }

        $dir->addChild(new vfsStreamDirectory($name));

        $child = $dir->getChild($name);

        if ($chmod !== null) {
            $child->chmod($chmod);
        }

        return $child->url();
    }

    /**
     * {@inheritdoc}
     */
    protected function createFileContent(int $size): string
    {
        return LargeFileContent::withKilobytes($size)->content();
    }

    /**
     * @param string $dottedDirs
     *
     * @return \org\bovigo\vfs\vfsStreamContent|\org\bovigo\vfs\vfsStreamDirectory
     */
    private function getDir(string $dottedDirs)
    {
        $folders = \explode('.', $dottedDirs);
        $dir = $folders[0] === 'root' ? $this->root : $this->root->getChild($folders[0]);

        if (\count($folders) !== 1) {
            unset($folders[0]);

            $nested = null;

            foreach ($folders as $folder) {
                if ($nested === null) {
                    $dir = $nested = $dir->getChild($folder);
                } else {
                    $dir = $nested = $nested->getChild($folder);
                }
            }

            unset($nested);
        }

        return $dir;
    }
}
