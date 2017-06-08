<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Traits;

use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

trait GuessFormatTrait
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * Guess format from file.
     *
     * @param string $filePath
     *
     * @return string|null
     */
    protected function guessFormat(string $filePath): ?string
    {
        $filePath = self::normalizeDirectorySeparator($filePath);

        if (is_file($filePath)) {
            return pathinfo($filePath, PATHINFO_EXTENSION);
        }

        return null;
    }
}
