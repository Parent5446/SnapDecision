<?php
/**
 * Created by PhpStorm.
 * User: parent5446
 * Date: 11/17/13
 * Time: 1:54 AM
 */

namespace SnapDecision\Controllers;


use SnapDecision\DI;
use SnapDecision\HttpException;
use SnapDecision\HttpRequest;
use SnapDecision\Response;


/**
 * Controller for authenticating with OAuth2 and storing user information.
 *
 * @package SnapDecision\Controllers
 */
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
	public function __construct( DI $deps ) {
		$this->deps = $deps;
	}

	/**
	 * @param array $params
	 *
	 * @throws \SnapDecision\HttpException To redirect the user
	 */
	public function get( array $params ) {
		if ( isset( $params['code'] ) ) {
			$this->deps->google->authenticate();
			$accessToken = $this->deps->google->getAccessToken();

			$identityClient = new \Google_Client( [
				'ioClass' => 'Google_HttpStreamIO',
				'cacheClass' => 'Google_MemcacheCache',
				// The memcached host and port do not matter since AppEngine
				// overrides them from app.yaml, but we need these values for the client
				// library to work
				'ioMemCacheCache_host' => 'invalid.domain',
				'ioMemCacheCache_port' => '37337',
			] );
			$identityClient->setApplicationName( 'SnapDecision' );
			$identityClient->setClientId( $this->deps->config['snapdecision']['clientid'] );
			$identityClient->setClientSecret( $this->deps->config['snapdecision']['clientsecret'] );
			$identityClient->setRedirectUri( $this->deps->config['snapdecision']['redirecturi'] );



			$identityClient->setScopes( [
				'https://www.googleapis.com/auth/glass.timeline',
				'https://www.googleapis.com/auth/glass.location',
				'https://www.googleapis.com/auth/userinfo.profile',
				'https://www.googleapis.com/auth/userinfo.email',
			] );

			$identityClient->setAccessToken( $accessToken );
			$identityService = new \Google_Oauth2Service( $identityClient );
			$user = $identityService->userinfo_v2_me->get();
			$userId = $user->getEmail();

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