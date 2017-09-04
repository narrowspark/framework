<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Encryption;

use Viserio\Component\Filesystem\Stream\MutableFile;
use Viserio\Component\Filesystem\Stream\ReadOnlyFile;

class File
{
    /**
     * Encrypt a file using key encryption.
     *
     * @param string|resource $input  File name or file handle
     * @param string|resource $output File name or file handle
     *
     * @throws InvalidType
     *
     * @return int Number of bytes written
     */
    public function encrypt($input, $output)
    {
    }

    /**
     * Decrypt a file using key encryption.
     *
     * @param string|resource $input  File name or file handle
     * @param string|resource $output File name or file handle
     *
     * @throws InvalidType
     *
     * @return bool TRUE if successful
     */
    public function decrypt($input, $output): bool
    {
    }
}
