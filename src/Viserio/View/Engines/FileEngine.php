<?php
declare(strict_types=1);
namespace Viserio\View\Engines;

use Viserio\Contracts\View\Engine as EngineContract;

class FileEngine implements EngineContract
{
    /**
     * {@inheritdoc}
     */
    public function get(array $fileInfo, array $data = []): string
    {
        return file_get_contents($fileInfo['path']);
    }
}
