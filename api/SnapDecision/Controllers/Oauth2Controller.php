<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/17/13
 * Time: 1:54 AM
 */

namespace SnapDecision\Controllers;


use SnapDecision\HttpException;
use SnapDecision\HttpRequest;
use SnapDecision\Response;

class Oauth2Controller {
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
	 * @param array $params
	 * @param null $data
	 *
	 * @throws \SnapDecision\HttpException To redirect the user
	 */
	public function get( array $params, $data ) {
		if ( isset( $params['code'] ) ) {
			$this->deps->google->authenticate( $params['code'] );
			$accessToken = $this->deps->google->getAccessToken();

			$this->deps->google->setAccessToken( $accessToken );
			$identityService = new \Google_Oauth2Service( $this->deps->google );
			$user = $identityService->userinfo->get();
			$userId = $user->getId();

			$insertStmt = $this->deps->stmtCache->prepare(
				'INSERT INTO users (user_id, access_token) VALUE (:userId, :accessToken)'
			);
			$insertStmt->bindParam( ":userId", $userId );
			$insertStmt->bindParam( ":accessToken", $accessToken );
			$insertStmt->execute();
		} else {
			throw new HttpException( 302, '', [ 'Location' => $this->deps->google->createAuthUrl() ] );
		}
	}
} 