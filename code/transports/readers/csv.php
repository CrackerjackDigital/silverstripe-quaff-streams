<?php
namespace Quaff\Transports\Readers;

use Quaff\Interfaces\Transport;

trait csv {
	abstract public function getBuffer();

	abstract public function current();

	abstract public function meta($key = null);

	public function read(&$responseCode = null) {
		$array = fgetcsv($this->getBuffer(), null, ",", '"');
		$responseCode = $this->meta(Transport::MetaResponseCode);
		return $array;
	}
}