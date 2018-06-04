<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Profiler\PanelAware as PanelAwareContract;
use Viserio\Component\Contract\Profiler\TooltipAware as TooltipAwareContract;
use Viserio\Component\Contract\Translation\Traits\TranslatorAwareTrait;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;

class ViserioTranslationDataCollector extends AbstractDataCollector implements
    TooltipAwareContract,
    PanelAwareContract
{
    use TranslatorAwareTrait;

    /**
     * Create new translation data collector.
     *
     * @param \Viserio\Component\Contract\Translation\Translator $translator
     */
    public function __construct(TranslatorContract $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $messages = $this->sanitizeCollectedMessages($this->translator->getCollectedMessages());

        $this->data = [
            'messages' => $messages,
            'counted'  => $this->computeCount($messages),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => \file_get_contents(__DIR__ . '/../Resource/icons/ic_translate_white_24px.svg'),
            'label' => '',
            'value' => $this->data['counted'][TranslatorContract::MESSAGE_DEFINED],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        return $this->createTooltipGroup([
            'Missing messages'  => $this->data['counted'][TranslatorContract::MESSAGE_MISSING],
            'Fallback messages' => $this->data['counted'][TranslatorContract::MESSAGE_EQUALS_FALLBACK],
            'Defined messages'  => $this->data['counted'][TranslatorContract::MESSAGE_DEFINED],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $sortedMessages = $this->getSortedMessages($this->data['messages']);

        $tableHeaders = [
            'Locale',
            'Domain',
            'Times used',
            'Message ID',
            'Message Preview',
        ];

        return $this->createTabs([
            [
                'name' => 'Defined <span class="counter">' .
                    $this->data['counted'][TranslatorContract::MESSAGE_DEFINED] .
                    '</span>',
                'content' => $this->createTable(
                    \array_values($sortedMessages[TranslatorContract::MESSAGE_DEFINED]),
                    [
                        'name'      => 'These messages are correctly translated into the given locale.',
                        'headers'   => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ],
            [
                'name' => 'Fallback <span class="counter">' .
                    $this->data['counted'][TranslatorContract::MESSAGE_EQUALS_FALLBACK] .
                    '</span>',
                'content' => $this->createTable(
                    \array_values($sortedMessages[TranslatorContract::MESSAGE_EQUALS_FALLBACK]),
                    [
                        'name'      => 'These messages are not available for the given locale but Narrowspark found them in the fallback locale catalog.',
                        'headers'   => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ],
            [
                'name' => 'Missing <span class="counter">' .
                    $this->data['counted'][TranslatorContract::MESSAGE_MISSING] .
                    '</span>',
                'content' => $this->createTable(
                    \array_values($sortedMessages[TranslatorContract::MESSAGE_MISSING]),
                    [
                        'name' => 'These messages are not available for the given locale and cannot be found in the fallback locales.' .
                        ' <br> Add them to the translation catalogue to avoid Narrowspark outputting untranslated contents.',
                        'headers'   => $tableHeaders,
                        'vardumper' => false,
                    ]
                ),
            ],
        ]);
    }

    /**
     * Get all collected messages.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    public function getMessages(): array
    {
        return $this->data['messages'] ?? [];
    }

    /**
     * Get counted messages.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    public function getCountedMessages(): array
    {
        return $this->data['counted'] ?? [];
    }

    /**
     * Sanitize collected messages.
     *
     * @param array $messages
     *
     * @return array
     */
    protected function sanitizeCollectedMessages(array $messages): array
    {
        $result = [];

        foreach ($messages as $key => $message) {
            $messageId = $message['locale'] . '.' . $message['domain'] . '.' . $message['id'];

            if (! isset($result[$messageId])) {
                $message['count']              = 1;
                $message['parameters']         = ! empty($message['parameters']) ? [$message['parameters']] : [];
                $messages[$key]['translation'] = $message['translation'];
                $result[$messageId]            = $message;
            } else {
                if (! empty($message['parameters'])) {
                    $result[$messageId]['parameters'][] = $message['parameters'];
                }

                $result[$messageId]['count']++;
            }
            unset($messages[$key]);
        }

        return $result;
    }

    /**
     * Counter for message types.
     *
     * @param array $messages
     *
     * @return array
     */
    protected function computeCount(array $messages): array
    {
        $count = [
            TranslatorContract::MESSAGE_DEFINED         => 0,
            TranslatorContract::MESSAGE_MISSING         => 0,
            TranslatorContract::MESSAGE_EQUALS_FALLBACK => 0,
        ];

        foreach ($messages as $message) {
            $count[$message['state']]++;
        }

        return $count;
    }

    /**
     * Sorte messages to the right type.
     *
     * @param array $messages
     *
     * @return array
     */
    protected function getSortedMessages(array $messages): array
    {
        $sortedMessages = [
            TranslatorContract::MESSAGE_MISSING         => [],
            TranslatorContract::MESSAGE_EQUALS_FALLBACK => [],
            TranslatorContract::MESSAGE_DEFINED         => [],
        ];

        foreach ($messages as $key => $value) {
            if ($value['state'] === TranslatorContract::MESSAGE_MISSING) {
                $sortedMessages[TranslatorContract::MESSAGE_MISSING][$value['id']] = [
                    $value['locale'],
                    $value['domain'],
                    $value['count'],
                    $value['id'],
                    $value['translation'],
                ];
            } elseif ($value['state'] === TranslatorContract::MESSAGE_EQUALS_FALLBACK) {
                $sortedMessages[TranslatorContract::MESSAGE_EQUALS_FALLBACK][$value['id']] = [
                    $value['locale'],
                    $value['domain'],
                    $value['count'],
                    $value['id'],
                    $value['translation'],
                ];
            } elseif ($value['state'] === TranslatorContract::MESSAGE_DEFINED) {
                $sortedMessages[TranslatorContract::MESSAGE_DEFINED][$value['id']] = [
                    $value['locale'],
                    $value['domain'],
                    $value['count'],
                    $value['id'],
                    $value['translation'],
                ];
            }
        }

        return $sortedMessages;
    }
}
