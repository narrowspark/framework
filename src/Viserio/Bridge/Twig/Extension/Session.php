<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;
use Viserio\Contracts\Session\Store as StoreContract;

class Session extends Twig_Extension
{
    /**
     * Viserio session instance.
     *
     * @var \Viserio\Contracts\Session\Store
     */
    protected $session;

    /**
     * Create a new session extension.
     *
     * @param \Viserio\Contracts\Session\Store
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
            new Twig_SimpleFunction('session', [$this->session, 'get']),
            new Twig_SimpleFunction('csrf_token', [$this->session, 'getToken'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('csrf_field', 'csrf_field', ['is_safe' => ['html']]),
            new Twig_SimpleFunction('method_field', 'method_field', ['is_safe' => ['html']]),
            new Twig_SimpleFunction('session_get', [$this->session, 'get']),
            new Twig_SimpleFunction('session_pull', [$this->session, 'pull']),
            new Twig_SimpleFunction('session_has', [$this->session, 'has']),
        ];
    }
}
