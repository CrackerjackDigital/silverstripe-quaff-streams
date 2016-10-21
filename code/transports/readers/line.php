<?php
namespace Quaff\Transports\Readers;

use Quaff\Transports\Transport;

/**
 * Trait to read a stream (or buffer) on a line-by-line basis
 *
 * @package Quaff\Transports\Buffers
 */
trait line {
	abstract public function getBuffer();

	abstract public function meta($key = null);

	abstract public function decode($line);

	/**
	 * @param mixed $responseCode
	 * @return string
	 */
	public function read(&$responseCode = null) {
		$line = $this->decode(fgets($this->getBuffer()));
		$responseCode = $this->meta(Transport::MetaResponseCode);
		return $line;
	}
}