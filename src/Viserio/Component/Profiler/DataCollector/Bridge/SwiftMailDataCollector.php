<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector\Bridge;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_Plugins_MessageLogger;
use Viserio\Component\Contract\Profiler\PanelAware as PanelAwareContract;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;

class SwiftMailDataCollector extends AbstractDataCollector implements PanelAwareContract
{
    /**
     * Swift_Plugins_MessageLogger instance.
     *
     * @var \Swift_Plugins_MessageLogger
     */
    protected $messagesLogger;

    /**
     * Create new swift mailer data collector instance.
     *
     * @param \Swift_Mailer $mailer
     */
    public function __construct(Swift_Mailer $mailer)
    {
        $this->messagesLogger = new Swift_Plugins_MessageLogger();

        $mailer->registerPlugin($this->messagesLogger);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $mails = [];

        foreach ($this->messagesLogger->getMessages() as $message) {
            $mails[] = [
                'to'      => $this->formatTo($message->getTo()),
                'subject' => $message->getSubject(),
                'headers' => $message->getHeaders()->toString(),
            ];
        }

        $this->data = [
            'count' => \count($mails),
            'mails' => $mails,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => 'ic_mail_outline_white_24px.svg',
            'label' => 'Mails',
            'value' => $this->data['count'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        return $this->createTable(
            $this->data['mails'],
            ['headers' => ['to', 'subject', 'headers']]
        );
    }

    /**
     * Format to from message.
     *
     * @param null|array $to
     *
     * @return string
     */
    protected function formatTo(?array $to): string
    {
        if (! $to) {
            return '';
        }

        $f = [];

        foreach ($to as $k => $v) {
            $f[] = (empty($v) ? '' : "${v} ") . "<${k}>";
        }

        return \implode(', ', $f);
    }
}
