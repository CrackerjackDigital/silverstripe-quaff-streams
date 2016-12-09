<?php
namespace Quaff\Transports\Buffers;

use Quaff\Transports\Transport;

trait passthru {
	private $_current;
	private $_uri;

	abstract public function read(&$responseCode = null);

	abstract public function setBuffer($stream);

	abstract public function getBuffer();

	/**
	 * Try to open a uri for the specified action, return a stream pointer. Doesn't mutate the internal _stream.
	 *
	 * @param string      $uri       local filename or url including query string etc
	 * @param string      $forAction e.g. Transport::ActionRead
	 * @param mixed       $responseCode from stream wrapper or generic ResponseDecodeOK/ResponseDecodeError
	 * @param string|null $contentType
	 * @param int|null    $contentLength
	 * @return bool|resource open stream resource or false if failed
	 * @throws \Quaff\Exceptions\Transport
	 */
	public function open($uri, $forAction, &$responseCode = null, &$contentType = null, &$contentLength = null) {
		if ($fp = fopen($uri, 'r', false, $this->context($forAction))) {
			if ($meta = stream_get_meta_data($fp)) {
				if (isset($meta['wrapper_data'])) {
					$metaData = $meta['wrapper_data'];

					$responseCode = $this->decodeMetaData($metaData, Transport::MetaResponseCode);
					$contentType = $this->decodeMetaData($metaData, Transport::MetaContentType);
					$contentLength = $this->decodeMetaData($metaData, Transport::MetaContentLength);
				} else {
					$responseCode = Transport::response_decode_ok();
					$contentType = $this->contentTypeFromURI($uri);
					$contentLength = $this->is_local($uri) ? filesize($uri) : null;
				}
			}
		} else {
			$responseCode = Transport::response_decode_error();
			$contentType = null;
			$contentLength = null;
		}
		return $fp;
	}

	/**
	 * passthru trait buffer method doesn't buffer, reading is 'real time' from opened stream.
	 *
	 * @param string $uri local filename or url including query string etc
	 * @param null   $responseCode
	 * @param null   $contentType
	 * @param null   $contentLength
	 * @return boolean
	 */
	public function buffer($uri, &$responseCode = null, &$contentType = null, &$contentLength = null) {
		$this->_uri = null;
		// check it exists first
		if ($this->ping($uri, $responseCode, $contentType, $contentLength)) {
			// now re-open with 'read' options
			$this->_uri = $uri;
			$this->setBuffer($this->open($uri, Transport::ActionRead, $responseCode, $contentType, $contentLength));
			return true;
		}
		return false;
	}

	/**
	 * Check we can open the uri with ActionExists flags and get some meta data about it. Doesn't alter the buffer's stream.
	 *
	 * @param string $uri
	 * @param mixed  $responseCode
	 * @param string $contentType
	 * @param int    $contentLength
	 * @return bool
	 */
	public function ping($uri, &$responseCode = null, &$contentType = null, &$contentLength = null) {
		if ($fp = $this->open($uri, Transport::ActionExists, $responseCode, $contentType, $contentLength)) {
			fclose($fp);
			return true;
		}
		return false;
	}

	public function getURI() {
		return $this->_uri;
	}

	public function key() {
		return md5($this->current());
	}

	public function current() {
		return $this->_current;
	}

	public function next() {
		$this->_current = $this->read();
	}

	public function context($forAction, array $moreOptions = []) {
		return stream_context_create($this->native_options($forAction, $moreOptions));
	}

	public function discard() {
		$stream = $this->getBuffer();
		if ($stream) {
			fclose($stream);
			$this->setBuffer(null);
		}
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSeekable() {
		$stream = $this->getBuffer();
		if ($meta = stream_get_meta_data($stream)) {
			return array_key_exists('seekable', $meta) && $meta['seekable'];
		}
		return false;
	}

	public function rewind() {
		$stream = $this->getBuffer();
		if ($stream && $this->isSeekable()) {
			// we can seek so do so
			fseek($stream, 0, SEEK_SET);
		} else {
			// reopen the uri instead
			$this->discard();
			$this->open($this->getURI(), self::ActionRead);
		}
		return $this;
	}

	public function valid() {
		$stream = $this->getBuffer();
		return $stream && !feof($stream);
	}

}