<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/16/13
 * Time: 3:29 PM
 */

namespace SnapDecision\Controllers;

/**
 * Class SearchController
 *
 * @package SnapDecision\Controllers
 */
class SearchController
{
	/**
	 * Dependency injection container
	 *
	 * @var \SnapDecision\DI
	 */
	private $deps;

	/**
	 * Construct the controller with the DI container
	 *
	 * @param \SnapDecision\DI $deps
	 */
	public function __construct( \SnapDecision\DI $deps ) {
		$this->deps = $deps;
	}

	/**
	 * Fetch information from a URN
	 *
	 * @param array $params URL parameters
	 * @param mixed $data Request data
	 */
	public function get( array $params, $data ) {

	}
}