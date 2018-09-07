<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Integration;

use Http\Psr7Test\RequestIntegrationTest;
use Viserio\Component\Http\Request;
use Viserio\Component\Http\Tests\Integration\Traits\BuildTrait;

/**
 * @internal
 */
final class RequestTest extends RequestIntegrationTest
{
    use BuildTrait;

    /**
     * Can be skipped on php7 and type save methods.
     *
     * @var array
     */
    protected $skippedTests = [
        'testMethodWithInvalidArguments'      => true,
        'testWithAddedHeaderInvalidArguments' => true,
        'testWithHeaderInvalidArguments'      => true,
    ];

    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        return new Request('/', 'GET');
    }
}
