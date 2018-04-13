<?php
declare(strict_types=1);
namespace Viserio\Component\Mail;

use Swift_Mailer;
use Swift_Transport;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Contract\Mail\Mailer as MailerContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\Support\Exception\InvalidArgumentException;
use Viserio\Component\Contract\View\Traits\ViewAwareTrait;
use Viserio\Component\Support\AbstractConnectionManager;

class MailManager extends AbstractConnectionManager implements ProvidesDefaultOptionsContract
{
    use EventManagerAwareTrait;
    use ViewAwareTrait;

    /**
     * A TransportFactory instance.
     *
     * @var \Viserio\Component\Mail\TransportFactory
     */
    private $transportFactory;

    /**
     * @var
     */
    private $queueManager;

    /**
     * Create a new mail manager instance.
     *
     * @param iterable|\Psr\Container\ContainerInterface $data
     * @param \Viserio\Component\Mail\TransportFactory   $transportFactory
     */
    public function __construct($data, TransportFactory $transportFactory)
    {
        parent::__construct($data);

        $this->transportFactory = $transportFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'default' => 'array',
        ];
    }

    /**
     * @param $queueManager
     *
     * @return void
     */
    public function setQueueManager($queueManager): void
    {
        $this->queueManager = $queueManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\Mail\Exception\InvalidArgumentException
     *
     * @return \Viserio\Component\Contract\Mail\Mailer|\Viserio\Component\Contract\Mail\QueueMailer
     */
    protected function create(array $config, string $method, string $errorMessage): MailerContract
    {
        if (isset($this->extensions[$config['name']])) {
            return $this->callCustomCreator($config['name'], $config);
        }

        if ($this->transportFactory->hasTransport($config['name'])) {
            return $this->createMailer(
                $this->transportFactory->getTransport(
                    $config['name'],
                    $config['transporter'] ?? []
                ),
                $config
            );
        }

        throw new InvalidArgumentException(\sprintf($errorMessage, $config['name']));
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigName(): string
    {
        return 'mail';
    }

    /**
     * Create a mailer or queue mailer instance.
     *
     * @param \Swift_Transport $transport
     * @param array            $config
     *
     * @return \Viserio\Component\Contract\Mail\Mailer|\Viserio\Component\Contract\Mail\QueueMailer
     */
    private function createMailer(Swift_Transport $transport, array $config): MailerContract
    {
        $swiftMailer = new Swift_Mailer($transport);

        if ($this->queueManager !== null) {
            $mailer = null;
        } else {
            $mailer = new Mailer($swiftMailer, $config);
        }

        if ($this->container !== null) {
            $mailer->setContainer($this->container);
        }

        if ($this->viewFactory !== null) {
            $mailer->setViewFactory($this->viewFactory);
        }

        if ($this->eventManager !== null) {
            $mailer->setEventManager($this->eventManager);
        }

        return $mailer;
    }
}
