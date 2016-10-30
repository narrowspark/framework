<?php
declare(strict_types=1);
namespace Viserio\Validation\Tests\Fixture;

class SuffixFixture
{
    public static function sanitize($value, $suffix = '')
    {
        return $value . ' ' . $suffix;
    }
}
