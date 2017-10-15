<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Displayer;

class JsonApiDisplayer extends JsonDisplayer
{
    /**
     * {@inheritdoc}
     */
    public function getContentType(): string
    {
        return 'application/vnd.api+json';
    }
}
