<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/16/13
 * Time: 1:41 AM
 */

namespace SnapDecision\ObjectCache;


/**
 * Wrapper object for retrieving values fetched from the ObjectCache.
 *
 * For some APIs, this may just hold the actual value. For others, it may
 * trigger waiting on an asynchronous call
 *
 * @package SnapDecision\ObjectCache
 */
abstract class ObjectCacheResult
{
	/**
	 * Whether the result is currently valid or not
	 *
	 * @var bool
	 */
	protected $valid = true;

	/**
	 * Fetch the next expected value of the function
	 *
	 * @return mixed
	 * @throws ObjectCacheException If the object is invalid
	 */
	abstract public function fetch();

	/**
	 * Fetch all values expected from the function
	 *
	 * @return array
	 * @throws ObjectCacheException If the object is invalid
	 */
	abstract public function fetchAll();

	/**
	 * Invalidates the object
	 *
	 * This is mainly for use by the parent ObjectCache class
	 * for invalidating result objects
	 */
	public function invalidate() {
		$this->valid = false;
	}
} 