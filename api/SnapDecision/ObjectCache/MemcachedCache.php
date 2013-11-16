<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/16/13
 * Time: 1:41 AM
 */

namespace SnapDecision\ObjectCache;


/**
 * Implementation of an object cache using Memcached and the PHP
 * Memcached library.
 *
 * @package SnapDecision\ObjectCache
 */
class MemcachedCache extends ObjectCache
{
	/**
	 * @var \Memcached
	 */
	private $memcached;

	/**
	 * @var MemcachedResult
	 */
	private $currResult = null;

	/**
	 * Construct a persistent Memcached connection
	 *
	 * @param array $servers Array of [host, port, weight], with the weight being optional
	 */
	public function __construct( array $servers ) {
		$this->memcached = new \Memcached( 'SnapDecision' );
		if ( !count( $this->memcached->getServerList() ) ) {
			$serializer = \Memcached::HAVE_IGBINARY ? \Memcached::SERIALIZER_IGBINARY : \Memcached::SERIALIZER_PHP;
			$this->memcached->addServers( $servers );
			$this->memcached->setOptions( [ \Memcached::OPT_SERIALIZER => $serializer, \Memcached::OPT_PREFIX_KEY => 'sd', \Memcached::OPT_BUFFER_WRITES => true, \Memcached::OPT_BINARY_PROTOCOL => true, \Memcached::OPT_NO_BLOCK => true, \Memcached::OPT_TCP_NODELAY => true, ] );
		}
	}

	/**
	 * Get a set of keys from memcached
	 *
	 * This invalidates the previous MemcachedResult, since the Memcached library
	 * only supports one delayed request at a time.
	 *
	 * @param array $keys Keys to fetch
	 *
	 * @return MemcachedResult
	 */
	function getMulti( array $keys ) {
		if ( $this->currResult ) {
			$this->currResult->invalidate();
		}

		$this->memcached->getDelayed( $keys );
		$this->currResult = new MemcachedResult( $this->memcached );

		return $this->currResult;
	}

	/**
	 * @param array $data
	 * @param int $expiration
	 */
	function setMulti( array $data, $expiration = 0 ) {
		$this->memcached->setMulti( $data, $expiration );
	}

	/**
	 * @param array $keys
	 */
	function deleteMulti( array $keys ) {
		$this->memcached->deleteMulti( $keys );
	}

	/**
	 * @param string $key
	 * @param int $offset
	 */
	function incr( $key, $offset = 1 ) {
		$this->memcached->increment( $key, $offset );
	}

	/**
	 * @param string $key
	 * @param int $offset
	 */
	function decr( $key, $offset = 1 ) {
		$this->memcached->decrement( $key, $offset );
	}
} 