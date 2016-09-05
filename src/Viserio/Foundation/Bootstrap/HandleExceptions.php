<?php
declare(strict_types=1);
namespace Viserio\Foundation\Bootstrap;

use Viserio\Contracts\Foundation\Application;
use Viserio\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Contracts\Exception\Handler as HandlerContract;
use Viserio\Exception\Providers\ExceptionServiceProvider;

class HandleExceptions extends AbstractLoadFiles implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        $app->register(new ExceptionServiceProvider());

        $handler = $app->get(HandlerContract::class);

        $handler->register();
    }
}
