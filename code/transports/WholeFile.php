<?php
namespace Quaff\Transports;

use Quaff\Transports\Buffers\passthru as buffer;
use Quaff\Transports\Decoders\passthru as decoder;
use Quaff\Transports\Readers\passthru as reader;
use Quaff\Transports\Stream\Stream;

/**
 * Fetch and read a file as a big block of content.
 *
 * @package Quaff\Transports
 */
class WholeFile extends Stream {
	use decoder;
	use reader;
	use buffer;
}