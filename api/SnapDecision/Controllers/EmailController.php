<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/17/13
 * Time: 1:41 AM
 */

namespace SnapDecision\Controllers;


use SnapDecision\HttpException;

/**
 * Processes and email and sends a timeline info to the suer
 *
 * @package SnapDecision\Controllers
 */
class EmailController {
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
	 * Process and email and send a notification to class
	 *
	 * @param array $data
	 * @param $params
	 * @return string Empty string
	 * @throws HttpException if the body is not wellformed
	 */
	public function post( array $data, $data ) {
		if ( !isset( $data['text/plain'] ) ) {
			throw new HttpException( 400 );
		}

		/** @var \SnapDecision\HttpRequest $request */
		$request = $data['text/plain'];
		$title = trim( $request->getBody() );

		$controller = new SearchController( $this->deps );
		$results = $controller->get( [ 'title' => $title ], null );

		$mirrorApi = new \Google_MirrorService( $this->deps->google );
		$timelineItem = new \Google_TimelineItem();
		$timelineItem->setText( $title );
		$mirrorApi->timeline->insert( $timelineItem );

		return '';
	}
}