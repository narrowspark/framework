<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extensions;

use Twig_Extension;
use Twig_Function;
use Viserio\Component\Contracts\Session\Store as StoreContract;

class SessionExtension extends Twig_Extension
{
    /**
     * Viserio session instance.
     *
     * @var \Viserio\Component\Contracts\Session\Store
     */
    protected $session;

    /**
     * Create a new session extension.
     *
     * @param \Viserio\Component\Contracts\Session\Store $session
     */
    public function __construct(StoreContract $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Viserio_Bridge_Twig_Extension_Session';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new Twig_Function('session', [$this->session, 'get']),
            new Twig_Function('csrf_token', [$this->session, 'getToken'], ['is_safe' => ['html']]),
            new Twig_Function('csrf_field', [$this, 'getCsrfField'], ['is_safe' => ['html']]),
            new Twig_Function('session_get', [$this->session, 'get']),
            new Twig_Function('session_has', [$this->session, 'has']),
        ];
    }

    public function getCsrfField()
    {
        return '<input type="hidden" name="_token" value="' . $this->session->getToken() . '">';
    }
}
