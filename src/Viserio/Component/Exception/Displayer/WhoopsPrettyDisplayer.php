<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Displayer;

use Interop\Http\Factory\ResponseFactoryInterface;
use Viserio\Component\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Whoops\Handler\Handler;
use Whoops\Handler\PrettyPageHandler;

class WhoopsDisplayer extends AbstractWhoopsDisplayer implements
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;
    use ResponseFactoryAwareTrait;

    /**
     * Configurations list for whoops.
     *
     * @var array
     */
    private $resolvedOptions;

    /**
     * Create a new whoops displayer instance.
     *
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param iterable|\Psr\Container\ContainerInterface     $data
     */
    public function __construct(ResponseFactoryInterface $responseFactory, $data = [])
    {
        parent::__construct($responseFactory);
        $this->resolvedOptions = self::resolveOptions($data);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'exception', 'whoops'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'debug_blacklist'   => [],
            'application_paths' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(): string
    {
        return 'text/html';
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose(): bool
    {
        return true;
    }

    /**
     * Get the Whoops handler.
     *
     * @return \Whoops\Handler\Handler
     */
    protected function getHandler(): Handler
    {
        $handler = new PrettyPageHandler();

        $handler->handleUnconditionally(true);

        foreach ($this->resolvedOptions['debug_blacklist'] as $key => $secrets) {
            foreach ($secrets as $secret) {
                $handler->blacklist($key, $secret);
            }
        }

        $handler->setApplicationPaths($this->resolvedOptions['application_paths']);

        return $handler;
    }
}
