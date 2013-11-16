<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/16/13
 * Time: 1:23 AM
 */

namespace SnapDecision\ObjectCache;

/**
 * Represents an object cache, such as memcached, for persistent storage of
 * temporary data
 *
 * @package SnapDecision\ObjectCache
 */
abstract class ObjectCache
{
	/**
	 * Gets the value of a certain key
	 *
	 * @param string $key Key value to retrieve
	 *
	 * @return ObjectCacheResult
	 * @throws ObjectCacheException on error
	 */
	public function get( $key ) {
		$res = $this->getMulti( [ $key ] );
		if ( isset( $res[$key] ) ) {
			return $res[$key];
		} else {
			return false;
		}
	}

	/**
	 * Get the values of multiple keys
	 *
	 * @param array $keys Keys to fetch
	 *
	 * @return ObjectCacheResult
	 * @throws ObjectCacheException on error
	 */
	abstract public function getMulti( array $keys );

	/**
	 * Set the value of a specific key
	 *
	 * @param string $key Key to set
	 * @param string $value Value to set
	 * @param int $expiry Expiry time
	 *
	 * @throws ObjectCacheException on error
	 */
	public function set( $key, $value, $expiry = 0 ) {
		$this->setMulti( [ $key => $value ], $expiry );
	}

	/**
	 * Sets the value of multiple keys
	 *
	 * @param array $data Mapping of key to value to set
	 * @param int $expiration Expiry of the keys
	 *
	 * @throws ObjectCacheException on error
	 */
	abstract public function setMulti( array $data, $expiration = 0 );

	/**
	 * Increment (or otherwise manipulate) the integer value of a key
	 *
	 * @param string $key Key to manipulate
	 * @param int $offset Amount to increment by (default is 1)
	 *
	 * @throws ObjectCacheException on error
	 */
	abstract public function incr( $key, $offset = 1 );

	/**
	 * Decrement (or otherwise manipulate) the integer value of a key
	 *
	 * @param string $key Key to manipulate
	 * @param int $offset Amount to decrement by (default is 1)
	 *
	 * @throws ObjectCacheException on error
	 */
	public function decr( $key, $offset = 1 ) {
		return $this->incr( $key, -$offset );
	}

	/**
	 * Delete a key from the object cache
	 *
	 * @param string $key Key to delete
	 *
	 * @throws ObjectCacheException on error
	 */
	public function delete( $key ) {
		return $this->deleteMulti( [ $key ] );
	}

	/**
	 * Delete a number of keys from the object cache
	 *
	 * @param array $keys Keys to delete
	 *
	 * @throws ObjectCacheException on error
	 */
	abstract public function deleteMulti( array $keys );
} 