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

use org\bovigo\vfs\content\LargeFileContent;

/**
 * @internal
 *
 * @small
 */
 final class FilesystemTest extends AbstractFilesystemTestCase
 {
     protected function tearDown(): void
     {
         parent::tearDown();

         \clearstatcache(false, $this->workspace);
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
         if ($at !== null) {
             $dir = $this->getDir($at);
         } else {
             $dir = $this->workspace;
         }

         $file = $dir . \DIRECTORY_SEPARATOR . $name;

         if ($content !== null) {
             \file_put_contents($file, $content);

             if ($time !== null) {
                 \touch($file, $time);
             }
         } elseif ($time !== null) {
             \touch($file, $time);
         } else {
             \touch($file);
         }

         if ($chgrp !== null) {
             \chgrp($file, $chgrp);
         }

         if ($chmod !== null) {
             $umask = \umask(0);
             \chmod($file, $chmod);
             \umask($umask);
         }

         return $file;
     }

     /**
      * {@inheritdoc}
      */
     protected function createDir(string $name, ?string $childOf = null, ?int $chmod = null): string
     {
         $dir = $this->workspace;

         if (! \is_dir($dir)) {
             \mkdir($dir, 0777, true);
         }

         if ($name === 'root') {
             if ($chmod !== null) {
                 \chmod($dir, $chmod);
             }

             return $dir;
         }

         if ($childOf !== null) {
             $dir = $this->getDir($childOf);
         }

         $child = $dir . \DIRECTORY_SEPARATOR . $name;

         if (! \is_dir($child)) {
             \mkdir($child, 0777, true);
         }

         if ($chmod !== null) {
             \chmod($child, $chmod);
         }

         return $child;
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
      * @return string
      */
     private function getDir(string $dottedDirs): string
     {
         $folders = \explode('.', $dottedDirs);
         $dir = $folders[0] === 'root' ? $this->workspace : $this->workspace . \DIRECTORY_SEPARATOR . $folders[0];

         if (\count($folders) !== 1) {
             unset($folders[0]);

             $nested = null;

             foreach ($folders as $folder) {
                 if ($nested === null) {
                     $nested = $dir .= \DIRECTORY_SEPARATOR . $folder . \DIRECTORY_SEPARATOR;
                 } else {
                     $dir = $nested .= \DIRECTORY_SEPARATOR . $folder . \DIRECTORY_SEPARATOR;
                 }
             }

             unset($nested);
         }

         return $dir;
     }
 }
