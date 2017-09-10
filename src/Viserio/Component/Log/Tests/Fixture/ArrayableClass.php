<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Fixture;

use Viserio\Component\Contract\Support\Arrayable;

class ArrayableClass implements Arrayable
{
    public function toArray(): array
    {
        return [
            'message' => true,
        ];
    }
}
