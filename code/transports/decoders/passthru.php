<?php
namespace Quaff\Transports\Decoders;

/**
 * passthru docoder just returns whatever it is given
 *
 * @package Quaff\Transports\decoders
 */
trait passthru {
	public function decode($line) {
		return $line;
	}
}