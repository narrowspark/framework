<?php
declare(strict_types=1);
namespace Viserio\Contracts\Mail;

interface GPGMailer extends Mailer
{
    /**
     * Import a public key, return the fingerprint
     *
     * @param string $gpgKey An ASCII armored public key
     *
     * @return string The GPG fingerprint for this key
     */
    public function import(string $gpgKey): string;

    /**
     * Get the public key corresponding to a fingerprint.
     *
     * @param string $fingerprint
     *
     * @return string
     */
    public function export(string $fingerprint): string;

    /**
     * Encrypt the body of an email.
     *
     * @param string $text
     *
     * @return string
     */
    public function decrypt($text): string;

    /**
     * Encrypt the body of an email.
     *
     * @param string $text
     * @param string $fingerprint
     *
     * @return string
     */
    public function encrypt($text, string $fingerprint): string;

    /**
     * Encrypt the body of an email.
     *
     * @param string $text
     * @param string $fingerprint
     *
     * @throws \Exception
     *
     * @return string
     */
    public function encryptAndSign($text, string $fingerprint): string;

    /**
     * Sets the private key for signing.
     *
     * @param string $serverKey
     *
     * @return GPGMailer
     */
    public function setPrivateKey(string $serverKey): GPGMailer;

    /**
     * Sign a message (but don't encrypt)
     *
     * @param string $text
     *
     * @throws \Exception
     *
     * @return string
     */
    public function sign($text): string;

    /**
     * Verify a message
     *
     * @param string $text
     * @param string $fingerprint
     *
     * @throws \Exception
     *
     * @return string
     */
    public function verify($text, string $fingerprint): bool;
}
