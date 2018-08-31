<?php
declare(strict_types=1);
namespace Viserio\Component\Translation;

use Viserio\Component\Contract\Translation\Exception\LogicException;
use Viserio\Component\Contract\Translation\MessageCatalogue as MessageCatalogueContract;
use Viserio\Component\Translation\Traits\ValidateLocaleTrait;

class MessageCatalogue implements MessageCatalogueContract
{
    use ValidateLocaleTrait;

    /**
     * List of messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Message catalogue instance.
     *
     * @var \Viserio\Component\Contract\Translation\MessageCatalogue
     */
    protected $fallbackCatalogue;

    /**
     * A string dictating the default language to translate into. (e.g. 'en').
     *
     * @var string
     */
    protected $locale = 'en';

    /**
     * A parent instance of MessageCatalogue.
     *
     * @var \Viserio\Component\Contract\Translation\MessageCatalogue
     */
    protected $parent;

    /**
     * Constructor.
     *
     * @param string $locale   The locale
     * @param array  $messages An array of messages classified by domain
     */
    public function __construct(string $locale, array $messages = [])
    {
        self::assertValidLocale($locale);

        $this->locale   = $locale;
        $this->messages = $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackCatalogue(): ?MessageCatalogueContract
    {
        return $this->fallbackCatalogue;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(MessageCatalogueContract $parent): MessageCatalogueContract
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomains(): array
    {
        return \array_keys($this->messages);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(string $domain = null): array
    {
        if ($domain === null) {
            return $this->messages;
        }

        return $this->messages[$domain] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $id, string $translation, string $domain = 'messages'): void
    {
        $this->add([$id => $translation], $domain);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id, string $domain = 'messages'): bool
    {
        if (isset($this->messages[$domain][$id])) {
            return true;
        }

        if ($this->fallbackCatalogue !== null) {
            return $this->fallbackCatalogue->has($id, $domain);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function defines(string $id, string $domain = 'messages'): bool
    {
        return isset($this->messages[$domain][$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id, string $domain = 'messages'): string
    {
        if (isset($this->messages[$domain][$id])) {
            return $this->messages[$domain][$id];
        }

        if ($this->fallbackCatalogue !== null) {
            return $this->fallbackCatalogue->get($id, $domain);
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $messages, string $domain = 'messages'): void
    {
        $this->messages[$domain] = [];
        $this->add($messages, $domain);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $messages, string $domain = 'messages'): void
    {
        unset($this->messages[$domain][$messages]);
    }

    /**
     * {@inheritdoc}
     */
    public function add(array $messages, string $domain = 'messages'): void
    {
        if (! isset($this->messages[$domain])) {
            $this->messages[$domain] = $messages;
        } else {
            $this->messages[$domain] = \array_replace($this->messages[$domain], $messages);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCatalogue(MessageCatalogueContract $catalogue): void
    {
        if ($catalogue->getLocale() !== $this->locale) {
            throw new LogicException(\sprintf(
                'Cannot add a catalogue for locale [%s] as the current locale for this catalogue is [%s].',
                $catalogue->getLocale(),
                $this->locale
            ));
        }

        foreach ($catalogue->getAll() as $domain => $messages) {
            $this->add($messages, $domain);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFallbackCatalogue(MessageCatalogueContract $catalogue): void
    {
        // detect circular references
        $circular = $this;

        do {
            if ($circular->getLocale() === $catalogue->getLocale()) {
                throw new LogicException(\sprintf(
                    'Circular reference detected when adding a fallback catalogue for locale [%s].',
                    $catalogue->getLocale()
                ));
            }
        } while ($circular = $circular->parent);

        $catalogue->setParent($this);

        $this->fallbackCatalogue = $catalogue;
    }
}
