<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Integration;

use Http\Psr7Test\ResponseIntegrationTest;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Tests\Integration\Traits\BuildTrait;

/**
 * @internal
 */
final class ResponseTest extends ResponseIntegrationTest
{
    use BuildTrait;

    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        return new Response();
    }
}
