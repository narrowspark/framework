<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Translation;

interface MessageCatalogue
{
    /**
     * Gets the catalogue locale.
     *
     * @return string The locale
     */
    public function getLocale(): string;

    /**
     * Gets the domains.
     *
     * @return array An array of domains
     */
    public function getDomains(): array;

    /**
     * Gets the messages within a given domain.
     *
     * If $domain is null, it returns all messages.
     *
     * @param null|string $domain The domain name
     *
     * @return array An array of messages
     */
    public function getAll(string $domain = null): array;

    /**
     * Sets a message translation.
     *
     * @param string $id          The message id
     * @param string $translation The messages translation
     * @param string $domain      The domain name
     *
     * @return void
     */
    public function set(string $id, string $translation, string $domain = 'messages'): void;

    /**
     * Checks if a message has a translation.
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return bool true if the message has a translation, false otherwise
     */
    public function has(string $id, string $domain = 'messages'): bool;

    /**
     * Checks if a message has a translation (it does not take into account the fallback mechanism).
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return bool true if the message has a translation, false otherwise
     */
    public function defines(string $id, string $domain = 'messages'): bool;

    /**
     * Gets a message translation.
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return string The message translation
     */
    public function get(string $id, string $domain = 'messages'): string;

    /**
     * Sets translations for a given domain.
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     *
     * @return void
     */
    public function replace(array $messages, string $domain = 'messages'): void;

    /**
     * Removes a record.
     *
     * @param string $messages
     * @param string $domain
     *
     * @return void
     */
    public function remove(string $messages, string $domain = 'messages'): void;

    /**
     * Adds translations for a given domain.
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     *
     * @return void
     */
    public function add(array $messages, string $domain = 'messages'): void;

    /**
     * Merges translations from the given Catalogue into the current one.
     *
     * The two catalogues must have the same locale.
     *
     * @param \Viserio\Component\Contracts\Translation\MessageCatalogue $catalogue A MessageCatalogue instance
     *
     * @throws \LogicException
     *
     * @return void
     */
    public function addCatalogue(MessageCatalogue $catalogue): void;

    /**
     * Merges translations from the given Catalogue into the current one
     * only when the translation does not exist.
     *
     * This is used to provide default translations when they do not exist for the current locale.
     *
     * @param \Viserio\Component\Contracts\Translation\MessageCatalogue $catalogue A MessageCatalogue instance
     *
     * @throws \Viserio\Component\Contracts\Translation\Exception\LogicException
     *
     * @return void
     */
    public function addFallbackCatalogue(MessageCatalogue $catalogue): void;

    /**
     * Gets the fallback catalogue.
     *
     * @return null|\Viserio\Component\Contracts\Translation\MessageCatalogue A MessageCatalogue instance or null when no fallback has been set
     */
    public function getFallbackCatalogue(): ?MessageCatalogue;

    /**
     * Set parent.
     *
     * @param \Viserio\Component\Contracts\Translation\MessageCatalogue $parent
     *
     * @return $this
     */
    public function setParent(MessageCatalogue $parent): MessageCatalogue;
}
