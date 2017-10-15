<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Displayer;

use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Component\Contract\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Whoops\Handler\Handler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

class WhoopsDisplayer implements
    DisplayerContract,
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
        $this->responseFactory = $responseFactory;
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
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($code);

        foreach (\array_merge($headers, ['Content-Type' => $this->getContentType()]) as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        $body = $response->getBody();
        $body->write($this->getWhoops()->handleException($exception));
        $body->rewind();

        return $response->withBody($body);
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
    public function canDisplay(Throwable $original, Throwable $transformed, int $code): bool
    {
        return \class_exists(Whoops::class);
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
    private function getConfiguredHandler(): Handler
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

    /**
     * Returns the whoops instance.
     *
     * @return Whoops
     */
    private function getWhoops(): Whoops
    {
        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler($this->getConfiguredHandler());

        return $whoops;
    }
}
