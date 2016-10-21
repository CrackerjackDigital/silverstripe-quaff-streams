<?php
namespace Quaff\Transports\Stream;
use Quaff\Transports\Transport;

/**
 * General 'stream' type transport with stream & http Protocol support, no Buffers but somewhere to hang config off e.g. response_code_decodes.
 */
abstract class Stream extends Transport {
	use \Quaff\Transports\Protocol\stream;
	use \Quaff\Transports\Protocol\http;

	private static $response_code_decodes = [
		'2*' => self::ResponseDecodeOK,
	    '*' => self::ResponseDecodeError
	];
}