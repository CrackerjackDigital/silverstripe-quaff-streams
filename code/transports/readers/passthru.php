<?php
namespace Quaff\Transports\Readers;

use Quaff\Transports\Transport;

/**
 * Trait to read a stream (or buffer) as a whole chunk of content.
 *
 * @package Quaff\Transports\Buffers
 */
trait passthru {
	abstract public function getBuffer();

	abstract public function meta($key = null, $stream = null);

	abstract public function decode($content);

	/**
	 * @param $responseCode
	 * @return string
	 */
	public function read(&$responseCode = null) {
		return $this->decode($this->readAll($responseCode));
	}
	
	public function readAll(&$responseCode = null) {
		$content = stream_get_contents($this->getBuffer());
		$responseCode = $this->meta(Transport::MetaResponseCode);
		return $content;
	}
}