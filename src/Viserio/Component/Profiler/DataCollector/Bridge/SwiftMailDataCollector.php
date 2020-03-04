<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Profiler\DataCollector\Bridge;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_Plugins_MessageLogger;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;
use Viserio\Contract\Profiler\PanelAware as PanelAwareContract;

class SwiftMailDataCollector extends AbstractDataCollector implements PanelAwareContract
{
    /**
     * Swift_Plugins_MessageLogger instance.
     *
     * @var Swift_Plugins_MessageLogger
     */
    protected $messagesLogger;

    /**
     * Create new swift mailer data collector instance.
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
                'to' => $this->formatTo($message->getTo()),
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
            'icon' => 'ic_mail_outline_white_24px.svg',
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
     */
    protected function formatTo(?array $to): string
    {
        if (! $to) {
            return '';
        }

        $f = [];

        foreach ($to as $k => $v) {
            $f[] = (empty($v) ? '' : "{$v} ") . "<{$k}>";
        }

        return \implode(', ', $f);
    }
}
