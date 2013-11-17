<?php

require "bootstrap.php";

// Make the router
$router = new SnapDecision\Router( $di );
$router->register( "/_ah/mail/:email", '\SnapDecision\Controllers\EmailController' );
$router->register( "/oauth2", '\SnapDecision\Controllers\Oauth2Controller', [ 'code' => 'code' ] );
$router->register( "/search", '\SnapDecision\Controllers\SearchController', [ 'title' => 'title', 'urn' => 'urn' ] );

// Run the request
$router->executeMain();
