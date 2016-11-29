<?php
declare(strict_types=1);
namespace Viserio\Cookie\Tests\Middleware;

use Narrowspark\TestingHelper\Middleware\DelegateMiddleware;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Cookie\CookieJar;
use Viserio\Cookie\Middleware\EncryptedCookiesMiddleware;
use Viserio\Cookie\ResponseCookies;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;

class EncryptedCookiesMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
}
