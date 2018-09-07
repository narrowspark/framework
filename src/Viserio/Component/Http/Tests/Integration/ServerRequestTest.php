<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Integration;

use Http\Psr7Test\ServerRequestIntegrationTest;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\Http\Tests\Integration\Traits\BuildTrait;

/**
 * @internal
 */
final class ServerRequestTest extends ServerRequestIntegrationTest
{
    use BuildTrait;

    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        return new ServerRequest('/', 'GET', [], null, '1.1', $_SERVER);
    }
}
