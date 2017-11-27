<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Session\Exception;

use DomainException as BaseDomainException;

class DomainException extends BaseDomainException implements Exception
{
}
