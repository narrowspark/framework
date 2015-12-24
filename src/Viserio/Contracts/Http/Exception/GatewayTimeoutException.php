<?php
namespace Viserio\Contracts\Http\Exception;

class GatewayTimeoutException extends ServerErrorException
{
	/**
	 * @var string
	 */
	protected $message = '504 Gateway Timeout';

	/**
	 * @var int
	 */
	protected $code = 504;
}
