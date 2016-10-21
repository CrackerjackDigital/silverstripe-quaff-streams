<?php
namespace Quaff\Transports;

use Quaff\Transports\Buffers\passthru as buffer;
use Quaff\Transports\Decoders\passthru as decoder;
use Quaff\Transports\Readers\line as reader;
use Quaff\Transports\Stream\Stream;

/**
 * Fetch and read a file which is to be read line-by-line.
 *
 * @package Quaff\Transports
 */
class LineFile extends Stream {
	use decoder;
	use reader;
	use buffer;
}