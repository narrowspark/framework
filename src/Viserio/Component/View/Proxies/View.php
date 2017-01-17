<?php
declare(strict_types=1);
namespace Viserio\Component\View\Proxies;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Viserio\Component\StaticalProxy\StaticalProxy;
use Viserio\Component\View\Factory;

class View extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return Factory::class;
    }

    public static function createResponseView(string $template, array $args = [])
    {
        $response = self::$container->get(ResponseFactoryInterface::class)->createResponse();

        $stream = self::$container->get(StreamFactoryInterface::class)->createStream();
        $stream->write((string) self::$container->get(Factory::class)->create($template, $args));

        return $response->withBody($stream);
    }
}
