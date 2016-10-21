<?php
namespace Quaff\Transports\Readers;

use Quaff\Transports\Transport;

/**
 * Trait to read a stream (or buffer) on a line-by-line basis
 *
 * @package Quaff\Transports\Buffers
 */
trait content {
	abstract public function getBuffer();

	abstract public function meta($key = null, $stream = null);

	abstract public function decode($content);

	/**
	 * @param $responseCode
	 * @return string
	 */
	public function read(&$responseCode) {
		$content = $this->decode(stream_get_contents($this->getBuffer()));
		$responseCode = $this->meta(Transport::MetaResponseCode);
		return $content;
	}
}