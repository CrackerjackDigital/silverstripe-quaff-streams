<?php
namespace Quaff\Transports\Protocol;

use Quaff\Transports\Transport;
use Quaff\Exceptions\Transport as Exception;

trait stream {
	/** @var array php context options eg for stream_context_create */
	private static $native_options = [
		Transport::ActionExists => [
			'http' => [
				'method' => 'HEAD',
			],
			'file' => [],
		],
		Transport::ActionRead   => [
			'http' => [
				'method' => 'GET',
			],
			'file' => [],
		],
	];

	// uri prefixes which identify a remote file, including '://' as unlikely to be part of a local file path
	private static $remote_schemas = [
		'http://',
		'https://'
	];

	/**
	 * @return resource stream
	 */
	abstract public function getBuffer();

	abstract public function decodeMetaData($metaData, $key = null, $multiPartSeperator = ';', $multiPartIndex = 0);

	abstract public function getURI();

	/**
	 * @param string|null $className
	 * @return \Config_ForClass
	 */
	abstract public function config($className = null);

	/**
	 * Check if a path starts with a configured remote schema, as a side-effect returns the first matched schema
	 *
	 * @param $uri
	 * @return string|bool first matched schema or false if not matched
	 */
	public static function is_remote($uri) {
		return current(
			array_filter(
				static::config()->get('remote_schemas') ?: [],
				function ($schema) use ($uri) {
					return strtolower(substr($uri, 0, strlen($schema))) == strtolower($schema);
				}
			)
		);
	}

	public static function is_local($uri) {
		return !static::is_remote($uri);
	}

	/**
	 * Return meta data from the stream wrapper either all or value which matches the key. If no meta data from stream wrapper and type is 'plainfile' then
	 * we use other ways to build a meta-data map.
	 *
	 * @param string $key
	 * @return string|array
	 * @throws \Quaff\Exceptions\Transport
	 */
	public function meta($key = null) {
		$metaData = [];

		if ($buffer = $this->getBuffer()) {
			if ($meta = stream_get_meta_data($buffer)) {

				if (isset($meta['wrapper_data'])) {
					$metaData = $this->decodeMetaData($meta['wrapper_data']);
				} else {
					if (isset($meta['wrapper_type'])) {
						if ($meta['wrapper_type'] == 'plainfile') {
							$filePathName = $this->getURI();
							$metaData = [
								Transport::MetaResponseCode => Transport::response_decode_ok(),
								Transport::MetaContentType  => mime_content_type($filePathName),
								Transport::MetaContentLength => filesize($filePathName)
							];
						}
					}
				}
				if (!is_null($key)) {
					if (array_key_exists($key, $metaData)) {
						return $metaData[ $key ];
					} else {
						throw new Exception("Unknown meta data key '$key'");
					}
				}
			}
		}
		return $metaData;
	}

}