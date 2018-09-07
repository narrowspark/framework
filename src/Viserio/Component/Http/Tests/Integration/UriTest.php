<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Integration;

use Http\Psr7Test\UriIntegrationTest;
use Viserio\Component\Http\Tests\Integration\Traits\BuildTrait;
use Viserio\Component\Http\Uri;

/**
 * @internal
 */
final class UriTest extends UriIntegrationTest
{
    use BuildTrait;

    /**
     * Can be skipped on php7 and type save methods.
     *
     * @var array
     */
    protected $skippedTests = [
        'testWithSchemeInvalidArguments' => true,
    ];

    /**
     * {@inheritdoc}
     */
    public function createUri($uri)
    {
        return Uri::createFromString($uri);
    }
}
