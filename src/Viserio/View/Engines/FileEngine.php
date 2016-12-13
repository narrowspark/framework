<?php
declare(strict_types=1);
namespace Viserio\View\Engines;

class FileEngine implements EngineInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(array $fileInfo, array $data = []): string
    {
        return file_get_contents($fileInfo['path']);
    }
}
