<?php
declare(strict_types=1);
namespace Viserio\Log\Tests\Fixture;

use Viserio\Contracts\Support\Jsonable;

class JsonableClass implements Jsonable
{
    public function toJson(int $options = 0): string
    {
        return json_encode([
            'message' => true,
        ], JSON_PRETTY_PRINT);
    }
}
