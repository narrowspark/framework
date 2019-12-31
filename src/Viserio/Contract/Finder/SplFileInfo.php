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

namespace Viserio\Contract\Finder;

/**
 * @method string getPath() @link https://php.net/manual/en/splfileinfo.getpath.php
 * @method string getFilename() @link https://php.net/manual/en/splfileinfo.getfilename.php
 * @method string getExtension() @link https://php.net/manual/en/splfileinfo.getextension.php
 * @method string getBasename(string $suffix = null) @link https://php.net/manual/en/splfileinfo.getbasename.php
 * @method string getPathname() @link https://php.net/manual/en/splfileinfo.getpathname.php
 * @method int getPerms() @link https://php.net/manual/en/splfileinfo.getperms.php
 * @method int getInode() @link https://php.net/manual/en/splfileinfo.getinode.php
 * @method int getSize() @link https://php.net/manual/en/splfileinfo.getsize.php
 * @method int getOwner() @link https://php.net/manual/en/splfileinfo.getowner.php
 * @method int getGroup() @link https://php.net/manual/en/splfileinfo.getgroup.php
 * @method int getATime() @link https://php.net/manual/en/splfileinfo.getatime.php
 * @method int getMTime() @link https://php.net/manual/en/splfileinfo.getmtime.php
 * @method int getCTime() @link https://php.net/manual/en/splfileinfo.getctime.php
 * @method string getType() @link https://php.net/manual/en/splfileinfo.gettype.php
 * @method bool isWritable() @link https://php.net/manual/en/splfileinfo.iswritable.php
 * @method bool isReadable() @link https://php.net/manual/en/splfileinfo.isreadable.php
 * @method bool isExecutable() @link https://php.net/manual/en/splfileinfo.isexecutable.php
 * @method bool isFile() @link https://php.net/manual/en/splfileinfo.isfile.php
 * @method bool isDir() @link https://php.net/manual/en/splfileinfo.isdir.php
 * @method bool isLink() @link https://php.net/manual/en/splfileinfo.islink.php
 * @method string getLinkTarget() @link https://php.net/manual/en/splfileinfo.getlinktarget.php
 * @method false|string getRealPath() @link https://php.net/manual/en/splfileinfo.getrealpath.php
 * @method \SplFileInfo getFileInfo(string $class_name = null) @link https://php.net/manual/en/splfileinfo.getfileinfo.php
 * @method \SplFileInfo getPathInfo(string $class_name = null) @link https://php.net/manual/en/splfileinfo.getpathinfo.php
 * @method \SplFileInfo openFile(string $open_mode = 'r', bool $use_include_path = false, resource $context = null) @link https://php.net/manual/en/splfileinfo.openfile.php
 * @method void setFileClass(string $class_name = null) @link https://php.net/manual/en/splfileinfo.setfileclass.php
 * @method void setInfoClass(string $class_name = null) @link https://php.net/manual/en/splfileinfo.setinfoclass.php
 * @method string __toString() @link https://php.net/manual/en/splfileinfo.tostring.php
 */
interface SplFileInfo
{
    /**
     * Returns the relative path.
     *
     * This path does not contain the file name.
     *
     * @return string the relative path
     */
    public function getRelativePath(): string;

    /**
     * Returns the relative path name.
     *
     * This path contains the file name.
     *
     * @return string the relative path name
     */
    public function getRelativePathname(): string;

    /**
     * Get sub path.
     *
     * @return string The sub path (sub directory)
     */
    public function getSubPath(): string;

    /**
     * Returns the relative sub path name.
     *
     * @return string the relative path name
     */
    public function getSubPathname(): string;

    /**
     * @param string $directory
     *
     * @throws \Viserio\Contract\Finder\Exception\NotFoundException
     *
     * @return string
     */
    public function getRelativeFilePathFromDirectory(string $directory): string;

    /**
     * Check if the file path ends with the given string.
     *
     * @param string $string
     *
     * @return bool
     */
    public function endsWith(string $string): bool;

    /**
     * Return the given path without a extension.
     *
     * @return string
     */
    public function getFilenameWithoutExtension(): string;

    /**
     * Returns the contents of the file.
     *
     * @throws \Viserio\Contract\Finder\Exception\RuntimeException
     *
     * @return string the contents of the file
     */
    public function getContents(): string;

    /**
     * Normalize the path.
     *
     * @return string
     */
    public function getNormalizedPathname(): string;

    /**
     * Normalize the real path.
     *
     * @return string
     */
    public function getNormalizedRealPath(): string;
}
