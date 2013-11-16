<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/16/13
 * Time: 1:48 AM
 */

namespace SnapDecision\ObjectCache;

/**
 * Result object for Memcached
 *
 * @package SnapDecision\ObjectCache
 */
class MemcachedResult extends ObjectCacheResult
{
	/**
	 * Memcached server object
	 *
	 * @var \Memcached
	 */
	private $memcached;

	/**
	 * @param \Memcached $memcached
	 */
	public function __construct( \Memcached $memcached ) {
		$this->memcached = $memcached;
	}

	/**
	 * @return mixed
	 * @throws ObjectCacheException
	 */
	function fetch() {
		if ( !$this->valid ) {
			throw new ObjectCacheException();
		}

		return $this->memcached->fetch();
	}

	/**
	 * @return array
	 * @throws ObjectCacheException
	 */
	function fetchAll() {
		if ( !$this->valid ) {
			throw new ObjectCacheException();
		}

		return $this->memcached->fetchAll();
	}
} 