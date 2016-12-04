<?php
declare(strict_types=1);
namespace Viserio\View\Proxies;

use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\StreamFactory;
use Viserio\StaticalProxy\StaticalProxy;
use Viserio\View\Factory;

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

    public static function createResponseView(string $template)
    {
        $response = (new ResponseFactory())->createResponse();

        $stream = (new StreamFactory())->createStream();
        $stream->write((string) self::$container->get(Factory::class)->create($template));

        return $response->withBody($stream);
    }
}
