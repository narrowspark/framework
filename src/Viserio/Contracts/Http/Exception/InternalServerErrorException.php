<?php
namespace Viserio\Contracts\Http\Exception;

class InternalServerErrorException extends ServerErrorException
{
	/**
	 * @var string
	 */
	protected $message = '500 Internal Server Error';

	/**
	 * @var int
	 */
	protected $code = 500;
}
