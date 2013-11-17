<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/17/13
 * Time: 1:41 AM
 */

namespace SnapDecision\Controllers;


use SnapDecision\HttpException;
use SnapDecision\HttpRequest;
use SnapDecision\DI;

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
	public function __construct( DI $deps ) {
		$this->deps = $deps;
	}

	/**
	 * Process and email and send a notification to class
	 *
	 * @param array $data
	 * @param \SnapDecision\HttpRequest $request
	 * @return string Empty string
	 * @throws HttpException if the body is not wellformed
	 */
	public function post( array $data, HttpRequest $request ) {
		/** @var \SnapDecision\HttpRequest $subRequest */
		$subRequest = $request->getBody();
		$addrSpec = '([^@<>:;]+@[^@<>:;]+)';
		$matches = [];
		preg_match( "/$addrSpec|(?:.*<$addrSpec>)/", $subRequest->getHeader( 'From' ), $matches );
		$fromEmail = $matches[1] ?: $matches[2];

		/** @var \SnapDecision\HttpRequest[] $versions */
		$versions = $subRequest->getBody();
		$email = $versions['text/plain'];
		$title = trim( $email->getBody() );

		$controller = new SearchController( $this->deps );
		$results = $controller->get( [ 'title' => $title ], null );

		$selectStmt = $this->deps->db->prepare(
			'SELECT access_token FROM users WHERE user_id = :userId'
		);
		$selectStmt->bindParam( ':userId', $fromEmail );
		$selectStmt->execute();
		$accessToken = $selectStmt->fetchColumn();

		$html = <<< HTML
<article>
  <figure>
    <img height="360px" src="{$results['image']}">
  </figure>
  <section>
    <h1 class="text-large">{$results['title']}</h1>
    <p class="text-x-small">
      ISBN: {$results['isbn']}
    </p>
    <hr>
    <p class="text-normal">
      Price: {$results['price']}
    </p>
  </section>
</article>
HTML;

		$this->deps->google->setAccessToken( $accessToken );
		$mirrorApi = new \Google_MirrorService( $this->deps->google );
		$timelineItem = new \Google_TimelineItem();
		$timelineItem->setTitle( $results['title'] );
		$timelineItem->setHtml( $html );

		$notificationConfig = new \Google_NotificationConfig();
		$notificationConfig->setLevel( 'AUDIO_ONLY' );
		$timelineItem->setNotification( $notificationConfig );

		$mirrorApi->timeline->insert( $timelineItem );

		return '';
	}
}