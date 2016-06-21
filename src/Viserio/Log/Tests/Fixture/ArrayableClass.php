<?php
namespace Viserio\Log\Tests\Fixture;

use Viserio\Contracts\Support\Arrayable;

class ArrayableClass implements Arrayable
{
    public function toArray(): array
    {
        return [
            'message' => true,
        ];
    }
}
