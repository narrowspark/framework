<?php
namespace Viserio\Translator;

use LogicException;
use Viserio\Contracts\Translator\MessageCatalogue as MessageCatalogueContract;
use Viserio\Translator\Traits\ValidateLocaleTrait;

class MessageCatalogue implements MessageCatalogueContract
{
    use ValidateLocaleTrait;

    /**
     * Messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Message catalogue instance.
     *
     * @var MessageCatalogueContract
     */
    protected $fallbackCatalogue;

    /**
     * A string dictating the default language to translate into. (e.g. 'en').
     *
     * @var string
     */
    protected $locale = 'en';

    /**
     *  Parent.
     *
     * @var MessageCatalogueContract
     */
    protected $parent;

    /**
     * Constructor.
     *
     * @param string $locale   The locale
     * @param array  $messages An array of messages classified by domain
     */
    public function __construct($locale, array $messages = [])
    {
        $this->assertValidLocale($locale);

        $this->locale = $locale;

        if (isset($messages['lang'])) {
            unset($messages['lang']);
        }

        $this->messages = $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomains()
    {
        return array_keys($this->messages);
    }

    /**
     * {@inheritdoc}
     */
    public function all($domain = null)
    {
        if (null === $domain) {
            return $this->messages;
        }

        return isset($this->messages[$domain]) ? $this->messages[$domain] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function set($id, $translation, $domain = 'messages')
    {
        $this->add([$id => $translation], $domain);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id, $domain = 'messages')
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
    public function defines($id, $domain = 'messages')
    {
        return isset($this->messages[$domain][$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, $domain = 'messages')
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
    public function replace($messages, $domain = 'messages')
    {
        $this->messages[$domain] = [];
        $this->add($messages, $domain);
    }

    /**
     * Removes a record.
     *
     * @param string|array $messages
     * @param string       $domain
     */
    public function remove($messages, $domain = 'messages')
    {
        unset($this->messages[$domain][$messages]);
    }

    /**
     * {@inheritdoc}
     */
    public function add($messages, $domain = 'messages')
    {
        if (!isset($this->messages[$domain])) {
            $this->messages[$domain] = $messages;
        } else {
            $this->messages[$domain] = array_replace($this->messages[$domain], $messages);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCatalogue(MessageCatalogueContract $catalogue)
    {
        if ($catalogue->getLocale() !== $this->locale) {
            throw new LogicException(sprintf(
                'Cannot add a catalogue for locale "%s" as the current locale for this catalogue is "%s"',
                $catalogue->getLocale(),
                $this->locale
            ));
        }

        foreach ($catalogue->all() as $domain => $messages) {
            $this->add($messages, $domain);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFallbackCatalogue(MessageCatalogueContract $catalogue)
    {
        // detect circular references
        $circular = $this;

        do {
            if ($circular->getLocale() === $catalogue->getLocale()) {
                throw new LogicException(sprintf(
                    'Circular reference detected when adding a fallback catalogue for locale "%s".',
                    $catalogue->getLocale()
                ));
            }
        } while ($circular = $circular->parent);

        $catalogue->setParent($this);

        $this->fallbackCatalogue = $catalogue;
    }

    /**
     * Set parent.
     *
     * @param MessageCatalogueContract $parent
     *
     * @return self
     */
    public function setParent(MessageCatalogueContract $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackCatalogue()
    {
        return $this->fallbackCatalogue;
    }
}
