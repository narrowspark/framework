<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Fixture;

use Viserio\Component\Contract\Parser\Parser as ParserContract;

class TextParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        return (array) $payload;
    }
}
